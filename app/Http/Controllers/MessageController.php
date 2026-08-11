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
        $response = $this->aiService->generateResponse($request->content);
      
        return redirect()->route('messages.index', $conversation);
    }
}