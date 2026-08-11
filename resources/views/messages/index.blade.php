<h1>{{ $conversation->title }}</h1>

<hr>

@foreach($messages as $message)

    <p>
        <strong>{{ ucfirst($message->role) }}:</strong>
        {{ $message->content }}
    </p>

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