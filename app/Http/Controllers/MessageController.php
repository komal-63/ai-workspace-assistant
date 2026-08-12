<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Services\AIService;

class MessageController extends Controller
{
    public function __construct(private AIService $aiService) 
    {
        
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
        $response = $this->aiService->generateResponse($messages);

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
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