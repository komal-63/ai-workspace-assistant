<x-app-layout>

    <style>

        /* =========================
           Design Tokens
           ========================= */

        .dashboard-page {

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

            min-height: calc(100vh - 68px);

            background: var(--paper);

            color: var(--ink);

            font-family: var(--font-body);
        }


        /* =========================
           Main Container
           ========================= */

        .dashboard-container {

            max-width: 900px;

            margin: 0 auto;

            padding: 55px 24px;
        }


        /* =========================
           Header
           ========================= */

        .dashboard-header {

            margin-bottom: 38px;
        }

        .dashboard-eyebrow {

            margin-bottom: 8px;

            color: var(--brass);

            font-size: 11px;

            font-weight: 700;

            letter-spacing: 0.12em;

            text-transform: uppercase;
        }

        .dashboard-header h1 {

            margin: 0;

            font-family: var(--font-display);

            font-size: 36px;

            font-weight: 600;

            letter-spacing: -0.02em;

            color: var(--ink);
        }

        .dashboard-header p {

            max-width: 600px;

            margin: 9px 0 0;

            color: var(--ink-soft);

            font-size: 14px;

            line-height: 1.6;
        }


        /* =========================
           Feature Cards
           ========================= */

        .dashboard-grid {

            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 16px;
        }


        .dashboard-card {

            display: flex;

            align-items: center;

            gap: 16px;

            min-height: 120px;

            padding: 20px;

            background: var(--paper-raised);

            border: 1px solid var(--line);

            border-radius: 12px;

            color: var(--ink);

            text-decoration: none;

            transition:
                background 0.15s ease,
                border-color 0.15s ease,
                transform 0.15s ease;
        }


        .dashboard-card:hover {

            background: var(--paper);

            border-color: #D9D0BF;

            transform: translateY(-2px);
        }


        /* =========================
           Card Icon
           ========================= */

        .dashboard-card-icon {

            width: 44px;

            height: 44px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 10px;

            background: var(--brass-soft);

            color: var(--brass);

            font-family: var(--font-display);

            font-size: 17px;

            font-weight: 600;
        }


        .dashboard-card.documents .dashboard-card-icon {

            background: var(--moss-soft);

            color: var(--moss);
        }


        .dashboard-card.admin .dashboard-card-icon {

            background: #ECE8E0;

            color: var(--ink);
        }


        /* =========================
           Card Content
           ========================= */

        .dashboard-card-content {

            flex: 1;
        }


        .dashboard-card-title {

            margin-bottom: 5px;

            font-family: var(--font-display);

            font-size: 18px;

            font-weight: 600;
        }


        .dashboard-card-description {

            color: var(--ink-soft);

            font-size: 12px;

            line-height: 1.5;
        }


        /* =========================
           Arrow
           ========================= */

        .dashboard-card-arrow {

            color: var(--ink-faint);

            font-size: 18px;

            transition:
                color 0.15s ease,
                transform 0.15s ease;
        }


        .dashboard-card:hover .dashboard-card-arrow {

            color: var(--brass);

            transform: translateX(3px);
        }


        /* =========================
           Admin Section
           ========================= */

        .admin-section {

            margin-top: 34px;
        }


        .section-label {

            margin-bottom: 12px;

            color: var(--ink-faint);

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 0.12em;
        }


        /* =========================
           Mobile
           ========================= */

        @media (max-width: 767px) {

            .dashboard-container {

                padding: 38px 16px;
            }


            .dashboard-header h1 {

                font-size: 30px;
            }


            .dashboard-grid {

                grid-template-columns: 1fr;
            }


            .dashboard-card {

                min-height: 105px;
            }
        }


        /* =========================
           Accessibility
           ========================= */

        .dashboard-card:focus-visible {

            outline: 2px solid var(--brass);

            outline-offset: 2px;
        }


        @media (prefers-reduced-motion: reduce) {

            .dashboard-page * {

                transition: none !important;
            }
        }

    </style>


    <div class="dashboard-page">

        <div class="dashboard-container">


            {{-- Page Header --}}

            <div class="dashboard-header">

                <div class="dashboard-eyebrow">
                    AI Workspace
                </div>

                <h1>
                    Welcome back, {{ Auth::user()->name }}
                </h1>

                <p>
                    Your workspace for document-grounded AI conversations.
                    Choose where you would like to continue.
                </p>

            </div>


            {{-- Main Features --}}

            <div class="dashboard-grid">


                {{-- Conversations --}}

                <a
                    href="{{ route('conversations.index') }}"
                    class="dashboard-card"
                >

                    <div class="dashboard-card-icon">
                        AI
                    </div>

                    <div class="dashboard-card-content">

                        <div class="dashboard-card-title">
                            Conversations
                        </div>

                        <div class="dashboard-card-description">
                            Start a new conversation or continue an existing chat.
                        </div>

                    </div>

                    <div class="dashboard-card-arrow">
                        →
                    </div>

                </a>


                {{-- Documents --}}

                <a
                    href="{{ route('documents.index') }}"
                    class="dashboard-card documents"
                >

                    <div class="dashboard-card-icon">
                        D
                    </div>

                    <div class="dashboard-card-content">

                        <div class="dashboard-card-title">
                            My Documents
                        </div>

                        <div class="dashboard-card-description">
                            Upload, manage and use your documents with AI.
                        </div>

                    </div>

                    <div class="dashboard-card-arrow">
                        →
                    </div>

                </a>


            </div>


            {{-- Admin Section --}}

            @if(Auth::user()->role === 'admin')

                <div class="admin-section">

                    <div class="section-label">
                        ADMINISTRATION
                    </div>


                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="dashboard-card admin"
                    >

                        <div class="dashboard-card-icon">
                            A
                        </div>

                        <div class="dashboard-card-content">

                            <div class="dashboard-card-title">
                                Admin Dashboard
                            </div>

                            <div class="dashboard-card-description">
                                View workspace statistics and manage users.
                            </div>

                        </div>

                        <div class="dashboard-card-arrow">
                            →
                        </div>

                    </a>

                </div>

            @endif


        </div>

    </div>

</x-app-layout>