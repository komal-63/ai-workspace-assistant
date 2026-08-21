<h1>{{ $conversation->title }}</h1>

<hr>

@foreach($messages as $message)

    <div style="margin-bottom: 20px;">

        <strong>{{ ucfirst($message->role) }}:</strong>

        @if($message->role === 'assistant')

            @if($message->source === 'document')
                <div style="font-size: 13px; color: green; margin: 5px 0;">
                    📄 Based on your document
                </div>
            @elseif($message->source === 'ai')
                <div style="font-size: 13px; color: blue; margin: 5px 0;">
                    🤖 AI Generated
                </div>
            @endif

        @endif

        <div>
            {{ $message->content }}
        </div>

    </div>

@endforeach

<hr>

<form method="POST" action="{{ route('messages.store', $conversation) }}">
    @csrf

    <textarea
        name="content"
        placeholder="Ask something..."
    ></textarea>

    <button type="submit">
        Send
    </button>
</form>