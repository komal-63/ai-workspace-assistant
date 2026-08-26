<x-app-layout>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600;700&display=swap');

    /* =========================
       Design tokens
       ========================= */

    .chat-page {
        --paper: #FAF7F1;
        --paper-raised: #FFFFFF;
        --ink: #23241F;
        --ink-soft: #6B6A63;
        --ink-faint: #A6A399;
        --line: #E7E1D3;
        --brass: #9C6B30;
        --brass-soft: #F1E6D2;
        --moss: #55694A;
        --moss-soft: #E5EBDD;
        --rust: #A2503A;
        --rust-soft: #F3E2DA;

        --font-display: 'Fraunces', Georgia, serif;
        --font-body: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* =========================
       Workspace Header
       ========================= */

    .workspace-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .workspace-icon {
        width: 40px;
        height: 40px;

        border-radius: 10px;

        background: var(--ink);
        color: var(--paper);

        display: flex;
        align-items: center;
        justify-content: center;

        font-family: var(--font-display);
        font-weight: 600;
        font-size: 15px;
        letter-spacing: 0.02em;
    }


    /* =========================
       New Conversation
       ========================= */

    .sidebar-actions {
        padding: 16px;
    }

    .new-conversation-btn {
        display: flex;
        align-items: center;
        justify-content: center;

        gap: 8px;

        width: 100%;

        padding: 10px 14px;

        border-radius: 8px;
        border: 1px solid var(--ink);

        background: var(--ink);
        color: var(--paper);

        font-family: var(--font-body);
        font-size: 13px;
        font-weight: 600;

        text-decoration: none;

        transition: background 0.15s ease, color 0.15s ease;
    }

    .new-conversation-btn:hover {
        background: var(--paper);
        color: var(--ink);
    }

    .new-conversation-btn span {
        font-size: 16px;
        line-height: 1;
        font-family: var(--font-display);
    }


    /* =========================
       Conversation List
       ========================= */

    .conversation-list {
        flex: 1;

        overflow-y: auto;

        padding: 4px 10px;
    }

    .conversation-section-title {
        padding: 12px 10px 8px;

        font-family: var(--font-body);
        font-size: 10px;
        font-weight: 700;

        letter-spacing: 0.12em;

        color: var(--ink-faint);
    }


    /* =========================
       Conversation Item
       ========================= */

    .conversation-item {
        display: flex;
        align-items: center;

        gap: 10px;

        padding: 9px 10px;

        margin-bottom: 2px;

        border-radius: 8px;
        border-left: 2px solid transparent;

        color: var(--ink);

        text-decoration: none;

        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .conversation-item:hover {
        background: var(--paper);
        color: var(--ink);
    }


    /* Active Conversation */

    .conversation-item.active {
        background: var(--brass-soft);
        border-left-color: var(--brass);
        color: var(--ink);
    }

    .conversation-item.active .conversation-icon {
        background: var(--paper-raised);
    }


    /* Conversation Icon */

    .conversation-icon {
        width: 30px;
        height: 30px;

        flex-shrink: 0;

        border-radius: 7px;

        background: var(--paper);
        border: 1px solid var(--line);

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 13px;
    }


    /* Conversation Information */

    .conversation-info {
        min-width: 0;
        flex: 1;
    }

    .conversation-title {
        overflow: hidden;

        text-overflow: ellipsis;
        white-space: nowrap;

        font-family: var(--font-body);
        font-size: 13px;
        font-weight: 500;
    }

    .conversation-date {
        margin-top: 2px;

        font-family: var(--font-body);
        font-size: 10.5px;

        color: var(--ink-faint);
    }


    /* Empty */

    .no-conversations {
        padding: 24px 10px;

        text-align: center;

        font-family: var(--font-body);
        font-size: 12px;

        color: var(--ink-faint);
    }


    /* Footer */

    .sidebar-footer {
        padding: 14px;

        border-top: 1px solid var(--line);
    }

    .input-field-area {
    flex: 1;
    min-width: 0;
}

.input-error {
    border-color: var(--rust) !important;
    background: var(--rust-soft) !important;
}

.input-error:focus {
    border-color: var(--rust) !important;
    box-shadow: 0 0 0 3px var(--rust-soft) !important;
}

.validation-error {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
    padding-left: 4px;
    color: var(--rust);
    font-size: 11.5px;
    font-weight: 500;
}

.validation-error span {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--rust);
    color: white;
    font-size: 10px;
    font-weight: 700;
    flex-shrink: 0;
}
.send-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.send-btn:disabled:hover {
    background: var(--ink);
    color: var(--paper);
}
</style>
    <div class="chat-page">

        <div class="container-fluid h-100">

            <div class="row h-100">

                {{-- Sidebar --}}
                <aside class="col-md-3 col-lg-3 chat-sidebar">

                    {{-- Sidebar Header --}}
                    <div class="sidebar-header">

                        <div class="workspace-title">
                            <div class="workspace-icon">
                                AI
                            </div>

                            <div>
                                <h4>Workspace</h4>
                                <p>Answers, grounded in your documents</p>
                            </div>
                        </div>

                    </div>


                    {{-- New Conversation --}}
                    <div class="sidebar-actions">

                        <a
                            href="{{ route('conversations.index') }}"
                            class="new-conversation-btn"
                        >
                            <span>+</span>
                            New conversation
                        </a>

                    </div>


                    {{-- Conversations --}}
                    <div class="conversation-list">

                        <div class="conversation-section-title">
                            RECENT
                        </div>


                        @forelse($conversations as $item)

                            <a
                                href="{{ route('messages.index', $item) }}"
                                class="conversation-item
                                    {{ $conversation->id === $item->id ? 'active' : '' }}"
                            >

                                <div class="conversation-icon">
                                    ⁘
                                </div>

                                <div class="conversation-info">

                                    <div class="conversation-title">
                                        {{ $item->title }}
                                    </div>

                                    <div class="conversation-date">
                                        {{ $item->created_at->diffForHumans() }}
                                    </div>

                                </div>

                            </a>

                        @empty

                            <div class="no-conversations">
                                No conversations yet.
                            </div>

                        @endforelse

                    </div>


                    {{-- Bottom Navigation --}}
                    <div class="sidebar-footer">

                        <a
                            href="{{ route('conversations.index') }}"
                            class="back-link"
                        >
                            ← All conversations
                        </a>

                    </div>

                </aside>


                {{-- Main Chat Area --}}
                <main class="col-md-9 col-lg-9 chat-main">

                    {{-- Chat Header --}}
                    <div class="chat-header">

                        <div>
                            <h5>
                                {{ $conversation->title }}
                            </h5>

                            <small>
                                Workspace assistant
                            </small>
                        </div>

                        <a
                            href="{{ route('conversations.index') }}"
                            class="mobile-back-btn"
                        >
                            ←
                        </a>

                    </div>


                    {{-- Messages --}}
                    <div
                        class="messages-container"
                        id="messagesContainer"
                    >

                        @forelse($messages as $message)

                            @if($message->role === 'user')

                                {{-- User Message --}}
                                <div class="message-row user-row">

                                    <div class="message-wrapper">

                                        <div class="user-message">
                                            {{ $message->content }}
                                        </div>

                                        <div class="message-label user-label">
                                            You
                                        </div>

                                    </div>

                                </div>

                            @else

                                {{-- AI Message --}}
                                <div class="message-row ai-row">

                                    <div class="ai-avatar">
                                        AI
                                    </div>

                                    <div class="message-wrapper">

                                        <div class="ai-message">
                                            {{ $message->content }}
                                        </div>


                                        {{-- Source --}}
                                        <div class="source-container">

                                            @if($message->source === 'document')

                                                <span class="source-badge source-document">
                                                    <i></i>
                                                    Based on {{ $message->document?->title ?? 'an uploaded document' }}
                                                </span>

                                            @elseif($message->source === 'not_found')

                                                <span class="source-badge source-not-found">
                                                    <i></i>
                                                    Not found in your documents
                                                </span>

                                            @else

                                                <span class="source-badge source-ai">
                                                    <i></i>
                                                    General knowledge
                                                </span>

                                            @endif

                                        </div>

                                    </div>

                                </div>

                            @endif

                        @empty

                            {{-- Empty State --}}
                            <div class="empty-state">

                                <div class="empty-icon">
                                    ⁘
                                </div>

                                <h3>
                                    Start a conversation
                                </h3>

                                <p>
                                    Ask a question about your uploaded documents,
                                    Laravel, or anything else.
                                </p>

                            </div>

                        @endforelse

                    </div>


                    {{-- Message Input --}}
                    <div class="chat-input-area">

                        <form
                            method="POST"
                            action="{{ route('messages.store', $conversation) }}"
                            id="messageForm"
                        >
                            @csrf

                            <div class="input-wrapper">

                                <div class="input-field-area">

                                    <textarea
                                        name="content"
                                        id="messageInput"
                                        rows="1"
                                        placeholder="Ask something..."
                                        class="form-control message-input @error('content') input-error @enderror"
                                    >{{ old('content') }}</textarea>

                                    @error('content')
                                        <div class="validation-error">
                                            <span>!</span>
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                <button
                                    type="submit"
                                    class="btn send-btn"
                                    id="sendButton"
                                >
                                    Send
                                </button>

                            </div>

                        </form>

                        <p class="input-hint">
                            Answers may draw on your uploaded documents or general knowledge.
                        </p>

                    </div>

                </main>

            </div>

        </div>

    </div>


    {{-- Custom CSS --}}
    <style>

        html,
        body {
            height: 100%;
        }

        * {
            box-sizing: border-box;
        }

        .chat-page {
            height: calc(100vh - 64px);
            background: var(--paper);
            font-family: var(--font-body);
            color: var(--ink);
        }

        .chat-sidebar {
            background: var(--paper-raised);
            border-right: 1px solid var(--line);
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .sidebar-header {
            padding: 22px 20px;
            border-bottom: 1px solid var(--line);
        }

        .sidebar-header h4 {
            margin: 0;
            font-family: var(--font-display);
            font-size: 19px;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .sidebar-header p {
            margin: 4px 0 0;
            color: var(--ink-soft);
            font-size: 12.5px;
        }

        .back-link {
            display: block;
            padding: 9px 12px;
            border-radius: 7px;
            color: var(--ink-soft);
            text-decoration: none;
            font-size: 13px;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .back-link:hover {
            background: var(--paper);
            color: var(--ink);
        }


        /* Main Chat */

        .chat-main {
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100%;
            background: var(--paper);
        }

        .chat-header {
            min-height: 72px;
            padding: 16px 32px;
            background: var(--paper-raised);
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-header h5 {
            margin: 0;
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 600;
            color: var(--ink);
        }

        .chat-header small {
            color: var(--ink-soft);
            font-size: 12px;
        }


        /* Messages */

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 24px 32px;
        }

        .message-row {
            display: flex;
            margin-bottom: 16px;
        }

        .user-row {
            justify-content: flex-end;
        }

        .ai-row {
            justify-content: flex-start;
            gap: 12px;
        }

       .user-row .message-wrapper {
            width: fit-content;
            max-width: 60%;
            margin-left: auto;
        }

        .ai-row .message-wrapper {
            width: fit-content;
            max-width: 65%;
        }

        /* User */

        .user-message {
            display: inline-block;
            width: fit-content;
            max-width: 100%;

            background: var(--ink);
            color: var(--paper);
            padding: 10px 15px;
            border-radius: 12px 12px 3px 12px;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .user-row .message-wrapper {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .user-label {
            text-align: right;
            margin-top: 5px;
            color: var(--ink-faint);
        }


        /* AI */

        .ai-avatar {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--ink);
            color: var(--paper);

            display: flex;
            align-items: center;
            justify-content: center;

            font-family: var(--font-display);
            font-weight: 600;
            font-size: 12px;

            flex-shrink: 0;
        }

        .ai-message {
            display: inline-block;
            width: fit-content;
            max-width: 100%;

            background: var(--paper-raised);
            border: 1px solid var(--line);
            border-left: 2px solid var(--brass);

            padding: 11px 15px;

            border-radius: 3px 12px 12px 12px;

            font-size: 14px;
            line-height: 1.6;
            color: var(--ink);

            white-space: pre-wrap;
        }

        .ai-row .message-wrapper {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .message-label {
            font-size: 11.5px;
            color: var(--ink-faint);
        }


        /* Source */

        .source-container {
            margin-top: 8px;
        }

        .source-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;

            padding: 4px 10px 4px 8px;

            border-radius: 20px;

            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .source-badge i {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .source-document {
            background: var(--moss-soft);
            color: var(--moss);
        }

        .source-document i {
            background: var(--moss);
        }

        .source-not-found {
            background: var(--rust-soft);
            color: var(--rust);
        }

        .source-not-found i {
            background: var(--rust);
        }

        .source-ai {
            background: var(--brass-soft);
            color: var(--brass);
        }

        .source-ai i {
            background: var(--brass);
        }


        /* Empty State */

        .empty-state {
            height: 100%;

            display: flex;
            flex-direction: column;

            justify-content: center;
            align-items: center;

            text-align: center;
        }

        .empty-icon {
            width: 64px;
            height: 64px;

            border-radius: 16px;

            background: var(--ink);
            color: var(--paper);

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 26px;
        }

        .empty-state h3 {
            margin-top: 18px;
            font-family: var(--font-display);
            font-size: 21px;
            font-weight: 600;
            color: var(--ink);
        }

        .empty-state p {
            max-width: 420px;
            margin-top: 4px;
            color: var(--ink-soft);
            font-size: 14px;
            line-height: 1.6;
        }


        /* Input */

        .chat-input-area {
            padding: 16px 32px;
            background: var(--paper-raised);
            border-top: 1px solid var(--line);
        }

        .input-wrapper {
            max-width: 900px;
            margin: auto;

            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .message-input {
            resize: none;
            min-height: 45px;
            max-height: 150px;

            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--paper);
            color: var(--ink);
            font-size: 14px;
            padding: 11px 14px;
        }

        .message-input:focus {
            outline: none;
            border-color: var(--brass);
            box-shadow: 0 0 0 3px var(--brass-soft);
            background: var(--paper-raised);
        }

        .send-btn {
            height: 45px;
            background: var(--ink);
            color: var(--paper);
            border: 1px solid var(--ink);
            border-radius: 10px;
            padding: 0 22px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .send-btn:hover {
            background: var(--paper);
            color: var(--ink);
        }

        .send-btn:focus-visible,
        .message-input:focus-visible,
        .new-conversation-btn:focus-visible,
        .conversation-item:focus-visible,
        .back-link:focus-visible {
            outline: 2px solid var(--brass);
            outline-offset: 2px;
        }

        .input-hint {
            max-width: 900px;
            margin: 8px auto 0;
            text-align: center;
            color: var(--ink-faint);
            font-size: 11px;
        }

        .mobile-back-btn {
            display: none;
        }


        /* Mobile */

        @media (max-width: 767px) {

            .chat-page {
                height: calc(100vh - 56px);
            }

            .chat-sidebar {
                display: none;
            }

            .chat-main {
                width: 100%;
            }

            .chat-header {
                padding: 14px 16px;
            }

            .mobile-back-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;

                text-decoration: none;

                border: 1px solid var(--line);

                border-radius: 8px;

                width: 34px;
                height: 34px;

                color: var(--ink);
            }

            .messages-container {
                padding: 20px 16px;
            }

            .message-wrapper,
            .user-row .message-wrapper,
            .ai-row .message-wrapper {
                max-width: 88%;
            }

            .chat-input-area {
                padding: 12px 16px;
            }

            .send-btn {
                padding: 0 16px;
            }

        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
            }
        }

    </style>


    {{-- JavaScript --}}
    <script>
    const messageInput = document.getElementById('messageInput');
    const messageForm = document.getElementById('messageForm');
    const messagesContainer = document.getElementById('messagesContainer');
    const sendButton = document.getElementById('sendButton');

    // Auto scroll to latest message
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Enable / disable Send button
    function updateSendButton() {
        if (!messageInput || !sendButton) {
            return;
        }

        const hasText = messageInput.value.trim().length > 0;

        sendButton.disabled = !hasText;
    }

    // Initial state
    updateSendButton();

    // Auto resize textarea + button state
    if (messageInput) {

        messageInput.addEventListener('input', function () {

            // Auto resize
            this.style.height = 'auto';

            this.style.height =
                Math.min(this.scrollHeight, 150) + 'px';

            // Update Send button
            updateSendButton();
        });

        // Enter = Send
        // Shift + Enter = New line
        messageInput.addEventListener('keydown', function (event) {

            if (
                event.key === 'Enter' &&
                !event.shiftKey
            ) {

                event.preventDefault();

                if (this.value.trim().length > 0) {
                    messageForm.requestSubmit();
                }
            }
        });
    }

    // Loading state
    if (messageForm) {

        messageForm.addEventListener('submit', function (event) {

            if (messageInput.value.trim().length === 0) {
                event.preventDefault();
                return;
            }

            sendButton.disabled = true;
            sendButton.innerHTML = 'Sending...';
        });
    }
</script>

</x-app-layout>