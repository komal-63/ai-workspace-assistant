<h1>{{ $conversation->title }}</h1>

<hr>

@foreach($messages as $message)

    <div style="margin-bottom: 20px;">

        <strong>{{ ucfirst($message->role) }}:</strong>

       @if($message->source === 'document')

            <small>
                📄 Based on:
                {{ $message->document?->title ?? 'Uploaded document' }}
            </small>

        @elseif($message->source === 'not_found')

            <small>
                🔍 Not found in uploaded documents
            </small>

        @else

            <small>
                🤖 AI Generated
            </small>

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