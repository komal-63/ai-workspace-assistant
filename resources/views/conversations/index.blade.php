<x-app-layout>

    <style>
        /* =========================
           Design Tokens
           ========================= */

        .conversations-page {
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

            --font-display: 'Fraunces', Georgia, serif;
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }


        /* =========================
           Page
           ========================= */

        .conversations-page {
            min-height: calc(100vh - 64px);
            background: var(--paper);
            font-family: var(--font-body);
            color: var(--ink);
        }


        /* =========================
           Main Container
           ========================= */

        .conversations-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 50px 24px;
        }


        /* =========================
           Page Header
           ========================= */

        .page-header {
            margin-bottom: 32px;
        }

        .page-eyebrow {
            margin-bottom: 8px;
            color: var(--brass);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .page-header h1 {
            margin: 0;
            font-family: var(--font-display);
            font-size: 34px;
            font-weight: 600;
            letter-spacing: -0.02em;
            color: var(--ink);
        }

        .page-header p {
            margin: 8px 0 0;
            max-width: 600px;
            color: var(--ink-soft);
            font-size: 14px;
            line-height: 1.6;
        }


        /* =========================
           Create Conversation
           ========================= */

        .create-card {
            background: var(--paper-raised);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 36px;
        }

        .create-card-title {
            margin-bottom: 14px;
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 600;
        }

        .create-form {
            display: flex;
            gap: 10px;
        }

        .conversation-title-input {
            flex: 1;
            min-width: 0;
            height: 44px;
            padding: 10px 13px;
            border: 1px solid var(--line);
            border-radius: 9px;
            background: var(--paper);
            color: var(--ink);
            font-family: var(--font-body);
            font-size: 13px;
        }

        .conversation-title-input::placeholder {
            color: var(--ink-faint);
        }

        .conversation-title-input:focus {
            outline: none;
            border-color: var(--brass);
            box-shadow: 0 0 0 3px var(--brass-soft);
            background: var(--paper-raised);
        }

        .create-btn {
            height: 44px;
            padding: 0 20px;
            border: 1px solid var(--ink);
            border-radius: 9px;
            background: var(--ink);
            color: var(--paper);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .create-btn:hover {
            background: var(--paper);
            color: var(--ink);
        }


        /* =========================
           Conversation Section
           ========================= */

        .conversation-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .conversation-section-title {
            color: var(--ink-faint);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
        }

        .conversation-count {
            color: var(--ink-faint);
            font-size: 11px;
        }


        /* =========================
           Conversation List
           ========================= */

        .conversation-list {
            background: var(--paper-raised);
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            text-decoration: none;
            color: var(--ink);
            transition: background 0.15s ease;
        }

        .conversation-item:last-child {
            border-bottom: none;
        }

        .conversation-item:hover {
            background: var(--paper);
            color: var(--ink);
        }


        /* =========================
           Conversation Icon
           ========================= */

        .conversation-icon {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: var(--brass-soft);
            color: var(--brass);
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 600;
        }


        /* =========================
           Conversation Info
           ========================= */

        .conversation-info {
            flex: 1;
            min-width: 0;
        }

        .conversation-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 14px;
            font-weight: 600;
        }

        .conversation-date {
            margin-top: 4px;
            color: var(--ink-faint);
            font-size: 11px;
        }


        /* =========================
           Arrow
           ========================= */

        .conversation-arrow {
            color: var(--ink-faint);
            font-size: 18px;
            transition: transform 0.15s ease, color 0.15s ease;
        }

        .conversation-item:hover .conversation-arrow {
            color: var(--brass);
            transform: translateX(2px);
        }


        /* =========================
           Empty State
           ========================= */

        .empty-state {
            padding: 55px 20px;
            text-align: center;
            background: var(--paper-raised);
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .empty-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: var(--ink);
            color: var(--paper);
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 600;
        }

        .empty-state h3 {
            margin: 0;
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 600;
        }

        .empty-state p {
            max-width: 400px;
            margin: 7px auto 0;
            color: var(--ink-soft);
            font-size: 13px;
            line-height: 1.6;
        }


        /* =========================
           Mobile
           ========================= */

        @media (max-width: 767px) {

            .conversations-page {
                min-height: calc(100vh - 56px);
            }

            .conversations-container {
                padding: 32px 16px;
            }

            .page-header h1 {
                font-size: 29px;
            }

            .create-form {
                flex-direction: column;
            }

            .conversation-title-input {
                width: 100%;
            }

            .create-btn {
                width: 100%;
            }

            .conversation-item {
                padding: 14px;
            }
        }


        /* =========================
           Accessibility
           ========================= */

        .conversation-title-input:focus-visible,
        .create-btn:focus-visible,
        .conversation-item:focus-visible {
            outline: 2px solid var(--brass);
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {

            * {
                transition: none !important;
            }
        }
    </style>


    <div class="conversations-page">

        <div class="conversations-container">

            {{-- Page Header --}}
            <div class="page-header">

                <div class="page-eyebrow">
                    AI Workspace
                </div>

                <h1>
                    Your conversations
                </h1>

                <p>
                    Start a new conversation or continue where you left off.
                    Your document-grounded AI conversations are kept here.
                </p>

            </div>


            {{-- Create Conversation --}}
            <div class="create-card">

                <div class="create-card-title">
                    Start a new conversation
                </div>

                <form
                    method="POST"
                    action="{{ route('conversations.store') }}"
                    class="create-form"
                >

                    @csrf

                    <input
                        type="text"
                        name="title"
                        placeholder="Give your conversation a title"
                        class="conversation-title-input"
                        required
                    >

                    <button
                        type="submit"
                        class="create-btn"
                    >
                        Create conversation
                    </button>

                </form>

            </div>


            {{-- Conversation List --}}
            <div class="conversation-section-header">

                <div class="conversation-section-title">
                    RECENT CONVERSATIONS
                </div>

                <div class="conversation-count">
                    {{ $conversations->count() }}
                    {{ $conversations->count() === 1 ? 'conversation' : 'conversations' }}
                </div>

            </div>


            @if($conversations->count())

                <div class="conversation-list">

                    @foreach($conversations as $conversation)

                        <a
                            href="{{ route('messages.index', $conversation) }}"
                            class="conversation-item"
                        >

                            <div class="conversation-icon">
                                ⁘
                            </div>

                            <div class="conversation-info">

                                <div class="conversation-title">
                                    {{ $conversation->title }}
                                </div>

                                <div class="conversation-date">
                                    Created {{ $conversation->created_at->diffForHumans() }}
                                </div>

                            </div>

                            <div class="conversation-arrow">
                                →
                            </div>

                        </a>

                    @endforeach

                </div>

            @else

                {{-- Empty State --}}
                <div class="empty-state">

                    <div class="empty-icon">
                        AI
                    </div>

                    <h3>
                        No conversations yet
                    </h3>

                    <p>
                        Create your first conversation above and start
                        asking questions about your documents or anything else.
                    </p>

                </div>

            @endif

        </div>

    </div>

</x-app-layout>