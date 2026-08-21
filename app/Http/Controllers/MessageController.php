<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Services\AIService;
use App\Services\EmbeddingService;
use App\Services\QdrantService;
use App\Models\Document;

class MessageController extends Controller
{
    public function __construct(
        private AIService $aiService,
        private EmbeddingService $embeddingService,
        private QdrantService $qdrantService
    ) {
    }

    public function index(Conversation $conversation)
    {
        abort_unless(
            $conversation->user_id === auth()->id(),
            403
        );

        $messages = $conversation->messages()
            ->oldest()
            ->get();

        return view('messages.index', compact('conversation', 'messages'));
    }

    public function store(Request $request, Conversation $conversation)
    {
        abort_unless(
            $conversation->user_id === auth()->id(),
            403
        );

        $request->validate([
            'content' => ['required', 'string'],
        ]);

        $conversation->messages()->create([
            'role' => 'user',
            'content' => $request->content,
        ]);

        $question = $request->content;

        $questionVector = $this->embeddingService->generate($question);

        $context = $this->qdrantService->search(
            $questionVector,
            auth()->id(),
            5
        );
        $documentIds = collect($context)
            ->pluck('payload.document_id')
            ->filter()
            ->unique()
            ->values();

        $documents = Document::whereIn('id', $documentIds)
            ->pluck('title', 'id');

        $messages = $conversation->messages()
            ->latest()
            ->take(20)
            ->get()
            ->reverse()
            ->map(function ($message) {
                return [
                    'role' => $message->role,
                    'content' => $message->content,
                ];
            })
            ->values()
            ->toArray();
        if ($conversation->summary) {
            array_unshift($messages, [
                'role' => 'system',
                'content' => "Conversation summary:\n" . $conversation->summary,
            ]);
        }
        $source = 'ai';
        $sourceDocument = null;
        if (empty($context)) {
            $response = $this->aiService->generateGeneralAnswer($question);
        } else {
            $response = $this->aiService->generateAnswer(
                $question,
                $context
            );
            $source = 'document';
            $sourceDocument = $documents->first();
        }
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
             'source' => $source,
        ]);

        $messageCount = $conversation->messages()->count();

        if ($messageCount > $conversation->summary_message_count) {

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

            if (!empty($newMessages)) {

                $summary = $this->aiService->generateSummary(
                    $conversation->summary ?? '',
                    $newMessages
                );

                $conversation->update([
                    'summary' => $summary,
                    'summary_message_count' => $conversation->summary_message_count + count($newMessages),
                ]);
            }
        }
        return redirect()->route('messages.index', $conversation);
    }
}