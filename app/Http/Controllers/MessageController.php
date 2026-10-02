<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Services\AIService;
use App\Services\RAGService;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    public function __construct(
        private AIService $aiService,
        private RAGService $ragService
    ) {
    }

    public function index(Conversation $conversation)
    {
        Gate::authorize('view', $conversation);

        $messages = $conversation->messages()
            ->oldest()
            ->get();

        $conversations = auth()->user()
            ->conversations()
            ->latest()
            ->get();

        return view('messages.index', compact(
            'conversation',
            'messages',
            'conversations'
        ));
    }

    public function stream(Request $request, Conversation $conversation)
    {
        Gate::authorize('view', $conversation);

        $request->validate([
            'content' => ['required', 'string'],
        ]);

        $question = trim((string) $request->input('content'));
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $question,
        ]);

        $messages = $this->buildMessageHistory($conversation);
        $source = 'ai';
        $documentId = null;
        $retrievalQuestion = $question;
        $context = [];
        $notFoundResponse = null;

        if ($this->ragService->isDocumentQuestion($question, $messages)) {
            $retrievalQuestion = $this->ragService->rewriteQuestionForRetrieval($question, $messages);
            $context = $this->ragService->retrieve($retrievalQuestion, auth()->id());

            if (empty($context)) {
                $source = 'not_found';
                $notFoundResponse = $this->aiService->generateNotFoundAnswer($question);
            } else {
                $grounded = $this->aiService->generateGroundedAnswer($retrievalQuestion, $context, $messages);

                if ($grounded['status'] === 'NONE') {
                    $source = 'not_found';
                    $notFoundResponse = $grounded['answer'];
                } else {
                    $source = 'document';
                    $documentId = $this->ragService->resolveSupportingDocumentId($context, $grounded);
                }
            }
        }

        $emit = function (string $event, array $payload) {
            echo "event: {$event}\n";
            echo 'data: ' . json_encode($payload, JSON_THROW_ON_ERROR) . "\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        };

        return response()->stream(function () use ($conversation, $question, $messages, $source, $documentId, $context, $retrievalQuestion, $notFoundResponse, $emit) {
            $fullText = '';
            $completed = false;

            try {
                $emit('meta', [
                    'source' => $source,
                    'document_id' => $documentId,
                    'status' => $source === 'not_found' ? 'not_found' : 'streaming',
                ]);

                if ($source === 'not_found') {
                    $fullText = $notFoundResponse ?? "I couldn't find this information in your uploaded documents.";
                    $emit('chunk', ['text' => $fullText]);
                } elseif ($source === 'document') {
                    $fullText = $this->aiService->streamGroundedAnswer(
                        $retrievalQuestion,
                        $context,
                        $messages,
                        function (string $chunk) use ($emit) {
                            $emit('chunk', ['text' => $chunk]);
                        }
                    );
                } else {
                    $fullText = $this->aiService->streamGeneralAnswer(
                        $question,
                        $messages,
                        function (string $chunk) use ($emit) {
                            $emit('chunk', ['text' => $chunk]);
                        }
                    );
                }

                $completed = true;
                $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $fullText,
                    'source' => $source,
                    'document_id' => $documentId,
                ]);

                $this->persistSummaryIfNeeded($conversation);
                $emit('done', ['status' => 'complete']);
            } catch (\Throwable $exception) {
                if (trim($fullText) !== '') {
                    $conversation->messages()->create([
                        'role' => 'assistant',
                        'content' => $fullText,
                        'source' => $source,
                        'document_id' => $documentId,
                    ]);
                    $this->persistSummaryIfNeeded($conversation);
                }

                $emit('error', ['message' => 'The response was interrupted before completion.']);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function store(Request $request, Conversation $conversation)
    {
        Gate::authorize('view', $conversation);

        $request->validate([
            'content' => ['required', 'string'],
        ]);

        $conversation->messages()->create([
            'role' => 'user',
            'content' => $request->content,
        ]);

        $question = $request->content;
        $messages = $this->buildMessageHistory($conversation);

        if ($conversation->summary) {
            array_unshift($messages, [
                'role' => 'system',
                'content' => "Conversation summary:\n" . $conversation->summary,
            ]);
        }
        $ragResult = $this->ragService->answer(
            $question,
            auth()->id(),
            $messages
        );

        $response = $ragResult['response'];
        $source = $ragResult['source'];
        $documentId = $ragResult['document_id'];

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
            'source' => $source,
            'document_id' => $documentId,
        ]);

        $this->persistSummaryIfNeeded($conversation);

        return redirect()->route('messages.index', $conversation);
    }

    private function buildMessageHistory(Conversation $conversation): array
    {
        return $conversation->messages()
            ->oldest()
            ->get()
            ->skip($conversation->summary_message_count)
            ->slice(0, -1)
            ->map(function ($message) {
                return [
                    'role' => $message->role,
                    'content' => $message->content,
                ];
            })
            ->values()
            ->toArray();
    }

    private function persistSummaryIfNeeded(Conversation $conversation): void
    {
        $messageCount = $conversation->messages()->count();
        $unsummarizedCount = $messageCount - $conversation->summary_message_count;

        if ($unsummarizedCount < 20) {
            return;
        }

        $newMessages = $conversation->messages()
            ->oldest()
            ->skip($conversation->summary_message_count)
            ->take(20)
            ->get()
            ->map(function ($message) {
                return [
                    'role' => $message->role,
                    'content' => $message->content,
                ];
            })
            ->toArray();

        if (empty($newMessages)) {
            return;
        }

        $summary = $this->aiService->generateSummary(
            $conversation->summary ?? '',
            $newMessages
        );

        $conversation->update([
            'summary' => $summary,
            'summary_message_count' =>
                $conversation->summary_message_count + count($newMessages),
        ]);
    }
}