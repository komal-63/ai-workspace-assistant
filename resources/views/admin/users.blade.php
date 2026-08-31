<x-app-layout>

    <style>

        /* =========================
           Design Tokens
           ========================= */

        .users-page {

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
           Container
           ========================= */

        .users-container {

            max-width: 900px;

            margin: 0 auto;

            padding: 50px 24px;
        }


        /* =========================
           Header
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
        }


        .page-header p {

            margin: 8px 0 0;

            color: var(--ink-soft);

            font-size: 14px;

            line-height: 1.6;
        }


        /* =========================
           Section Header
           ========================= */

        .users-section-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 12px;
        }


        .section-title {

            color: var(--ink-faint);

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 0.12em;
        }


        .user-count {

            color: var(--ink-faint);

            font-size: 11px;
        }


        /* =========================
           Users List
           ========================= */

        .users-list {

            background: var(--paper-raised);

            border: 1px solid var(--line);

            border-radius: 12px;

            overflow: hidden;
        }


        .user-item {

            display: flex;

            align-items: center;

            gap: 15px;

            padding: 17px 18px;

            border-bottom: 1px solid var(--line);

            text-decoration: none;

            color: var(--ink);

            transition: background 0.15s ease;
        }


        .user-item:last-child {

            border-bottom: none;
        }


        .user-item:hover {

            background: var(--paper);
        }


        /* =========================
           User Initial
           ========================= */

        .user-icon {

            width: 40px;

            height: 40px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 10px;

            background: var(--brass-soft);

            color: var(--brass);

            font-family: var(--font-display);

            font-size: 16px;

            font-weight: 600;

            text-transform: uppercase;
        }


        /* =========================
           User Information
           ========================= */

        .user-info {

            flex: 1;

            min-width: 0;
        }


        .user-name {

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            margin-bottom: 4px;

            font-size: 14px;

            font-weight: 600;
        }


        .user-email {

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            color: var(--ink-faint);

            font-size: 11px;
        }


        /* =========================
           Role Badge
           ========================= */

        .role-badge {

            flex-shrink: 0;

            padding: 5px 9px;

            border-radius: 6px;

            background: var(--paper);

            color: var(--ink-soft);

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 0.04em;

            text-transform: uppercase;
        }


        .role-badge.admin {

            background: var(--brass-soft);

            color: var(--brass);
        }


        .role-badge.manager {

            background: var(--moss-soft);

            color: var(--moss);
        }


        /* =========================
           Arrow
           ========================= */

        .user-arrow {

            flex-shrink: 0;

            color: var(--ink-faint);

            font-size: 18px;

            transition:
                color 0.15s ease,
                transform 0.15s ease;
        }


        .user-item:hover .user-arrow {

            color: var(--brass);

            transform: translateX(3px);
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

            .users-container {

                padding: 35px 16px;
            }


            .page-header h1 {

                font-size: 29px;
            }


            .user-item {

                padding: 14px;
            }


            .role-badge {

                display: none;
            }
        }


        /* =========================
           Accessibility
           ========================= */

        .user-item:focus-visible {

            outline: 2px solid var(--brass);

            outline-offset: -2px;
        }


        @media (prefers-reduced-motion: reduce) {

            .users-page * {

                transition: none !important;
            }
        }

    </style>


    <div class="users-page">

        <div class="users-container">


            {{-- Page Header --}}

            <div class="page-header">

                <div class="page-eyebrow">
                    Administration
                </div>

                <h1>
                    Users
                </h1>

                <p>
                    View workspace users and manage their access
                    and roles.
                </p>

            </div>


            {{-- Users Section --}}

            <div class="users-section-header">

                <div class="section-title">
                    WORKSPACE USERS
                </div>

                <div class="user-count">
                    {{ $users->count() }}
                    {{ $users->count() === 1 ? 'user' : 'users' }}
                </div>

            </div>


            @if($users->count())

                <div class="users-list">

                    @foreach($users as $user)

                        <a
                            href="{{ route('admin.users.show', $user) }}"
                            class="user-item"
                        >

                            {{-- User Icon --}}

                            <div class="user-icon">
                                {{ substr($user->name, 0, 1) }}
                            </div>


                            {{-- User Information --}}

                            <div class="user-info">

                                <div class="user-name">
                                    {{ $user->name }}
                                </div>

                                <div class="user-email">
                                    {{ $user->email }}
                                </div>

                            </div>


                            {{-- Role --}}

                            <div
                                class="role-badge {{ $user->role }}"
                            >
                                {{ $user->role }}
                            </div>


                            {{-- Arrow --}}

                            <div class="user-arrow">
                                →
                            </div>

                        </a>

                    @endforeach

                </div>

            @else

                <div class="empty-state">

                    <div class="empty-icon">
                        U
                    </div>

                    <h3>
                        No users found
                    </h3>

                    <p>
                        There are currently no users available
                        in the workspace.
                    </p>

                </div>

            @endif


        </div>

    </div>

</x-app-layout>