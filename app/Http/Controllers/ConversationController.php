<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ConversationController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Conversation::class);

        $conversations = auth()->user()
            ->conversations()
            ->latest()
            ->get();

        return view('conversations.index', compact('conversations'));
    }

   public function store(Request $request)
    {
        Gate::authorize('create', Conversation::class);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $conversation = auth()->user()->conversations()->create([
            'title' => $request->title,
        ]);

        return redirect()->route('messages.index', $conversation);
    }
}