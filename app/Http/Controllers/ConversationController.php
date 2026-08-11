<?php

namespace App\Http\Controllers;
use App\Models\Conversation;
use Illuminate\Http\Request;


class ConversationController extends Controller
{
     public function index()
    {
        $conversations = auth()->user()
            ->conversations()
            ->latest()
            ->get();

        return view('conversations.index', compact('conversations'));
    }

     public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        auth()->user()->conversations()->create([
            'title' => $request->title,
        ]);

        return redirect()->route('conversations.index');
    }
}
