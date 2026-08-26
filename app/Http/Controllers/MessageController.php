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

        $ragResult = $this->ragService->answer(
            $question,
            auth()->id()
        );

        $response = $ragResult['response'];
        $source = $ragResult['source'];
        $documentId = $ragResult['document_id'];

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

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
            'source' => $source,
            'document_id' => $documentId,
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
                    'summary_message_count' =>
                        $conversation->summary_message_count + count($newMessages),
                ]);
            }
        }

        return redirect()->route('messages.index', $conversation);
    }
}