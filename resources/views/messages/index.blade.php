<x-app-layout>

    <div class="chat-page">

        <div class="container-fluid h-100">

            <div class="row h-100">

                {{-- Sidebar --}}
                <aside class="col-md-3 col-lg-3 chat-sidebar">

                    <div class="sidebar-header">
                        <h4>AI Workspace</h4>

                        <p>Your conversations</p>
                    </div>

                    <div class="sidebar-content">

                        <a
                            href="{{ route('conversations.index') }}"
                            class="back-link"
                        >
                            ← All Conversations
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
                                AI Workspace Assistant
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
                                        🤖
                                    </div>

                                    <div class="message-wrapper">

                                        <div class="ai-message">
                                            {{ $message->content }}
                                        </div>


                                        {{-- Source --}}
                                        <div class="source-container">

                                            @if($message->source === 'document')

                                                <span class="source-badge source-document">
                                                    📄
                                                    Based on:
                                                    {{ $message->document?->title ?? 'Uploaded document' }}
                                                </span>

                                            @elseif($message->source === 'not_found')

                                                <span class="source-badge source-not-found">
                                                    🔍
                                                    Not found in uploaded documents
                                                </span>

                                            @else

                                                <span class="source-badge source-ai">
                                                    🤖
                                                    AI Generated
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
                                    🤖
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

                                <textarea
                                    name="content"
                                    id="messageInput"
                                    rows="1"
                                    placeholder="Ask something..."
                                    class="form-control message-input"
                                ></textarea>


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
                            AI responses may be generated from your uploaded
                            documents or general knowledge.
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

        .chat-page {
            height: calc(100vh - 64px);
            background: #f8f9fa;
        }

        .chat-sidebar {
            background: #ffffff;
            border-right: 1px solid #dee2e6;
            padding: 0;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid #dee2e6;
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .sidebar-header p {
            margin: 5px 0 0;
            color: #6c757d;
            font-size: 14px;
        }

        .sidebar-content {
            padding: 20px;
        }

        .back-link {
            display: block;
            padding: 10px 12px;
            border-radius: 8px;
            color: #495057;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            background: #f1f3f5;
        }


        /* Main Chat */

        .chat-main {
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .chat-header {
            min-height: 75px;
            padding: 18px 30px;
            background: #ffffff;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .chat-header small {
            color: #6c757d;
        }


        /* Messages */

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
        }

        .message-row {
            display: flex;
            margin-bottom: 18px;
        }

        .user-row {
            justify-content: flex-end;
        }

        .ai-row {
            justify-content: flex-start;
            gap: 10px;
        }

       .user-row .message-wrapper {
            max-width: 60%;
        }

        /* User */

        .user-message {
            background: #4f46e5;
            color: white;
            padding: 12px 17px;
            border-radius: 18px 18px 4px 18px;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .user-label {
            text-align: right;
            margin-top: 5px;
        }


        /* AI */

        .ai-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #e7e5ff;

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;
        }

        .ai-message {
            background: #ffffff;
            border: 1px solid #dee2e6;

            padding: 14px 18px;

            border-radius: 18px 18px 18px 4px;

            font-size: 14px;
            line-height: 1.7;

            white-space: pre-wrap;
        }

        .message-label {
            font-size: 12px;
            color: #adb5bd;
        }


        /* Source */

        .source-container {
            margin-top: 8px;
        }

        .source-badge {
            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;
            font-weight: 500;
        }

        .source-document {
            background: #e8f7ee;
            color: #198754;
        }

        .source-not-found {
            background: #fff3cd;
            color: #856404;
        }

        .source-ai {
            background: #e9ecef;
            color: #6c757d;
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
            width: 70px;
            height: 70px;

            border-radius: 18px;

            background: #e7e5ff;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 30px;
        }

        .empty-state h3 {
            margin-top: 18px;
            font-size: 22px;
            font-weight: 600;
        }

        .empty-state p {
            max-width: 450px;
            color: #6c757d;
            font-size: 14px;
        }


        /* Input */

        .chat-input-area {
            padding: 15px 30px;
            background: #ffffff;
            border-top: 1px solid #dee2e6;
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
        }

        .message-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }

        .send-btn {
            height: 45px;
            background: #4f46e5;
            color: white;
            padding: 0 22px;
            font-weight: 600;
        }

        .send-btn:hover {
            background: #4338ca;
            color: white;
        }

        .input-hint {
            max-width: 900px;
            margin: 8px auto 0;
            text-align: center;
            color: #adb5bd;
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
                padding: 15px;
            }

            .mobile-back-btn {
                display: inline-block;

                text-decoration: none;

                border: 1px solid #dee2e6;

                border-radius: 8px;

                padding: 5px 10px;

                color: #495057;
            }

            .messages-container {
                padding: 20px 15px;
            }

            .message-wrapper {
                max-width: 85%;
            }

            .chat-input-area {
                padding: 12px 15px;
            }

            .send-btn {
                padding: 0 15px;
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

            messagesContainer.scrollTop =
                messagesContainer.scrollHeight;

        }


        // Auto resize textarea

        if (messageInput) {

            messageInput.addEventListener('input', function () {

                this.style.height = 'auto';

                this.style.height =
                    Math.min(this.scrollHeight, 150) + 'px';

            });


            // Enter = Send
            // Shift + Enter = New line

            messageInput.addEventListener('keydown', function (event) {

                if (
                    event.key === 'Enter' &&
                    !event.shiftKey
                ) {

                    event.preventDefault();

                    messageForm.requestSubmit();

                }

            });

        }


        // Loading state

        if (messageForm) {

            messageForm.addEventListener('submit', function () {

                sendButton.disabled = true;

                sendButton.innerHTML = 'Sending...';

            });

        }

    </script>

</x-app-layout>