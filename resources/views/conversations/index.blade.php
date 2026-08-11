<h1>My Conversations</h1>

<form method="POST" action="{{ route('conversations.store') }}">
    @csrf

    <input
        type="text"
        name="title"
        placeholder="Conversation title"
    >

    <button type="submit">
        Create Conversation
    </button>
</form>

<hr>

@foreach($conversations as $conversation)

    <p>
        {{ $conversation->title }}
    </p>

@endforeach