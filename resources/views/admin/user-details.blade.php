<x-app-layout>

    <style>

        /* =========================
           Design Tokens
           ========================= */

        .user-details-page {

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

            --danger: #9A4A3A;
            --danger-soft: #F4E4DF;

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

        .details-container {

            max-width: 700px;

            margin: 0 auto;

            padding: 50px 24px;
        }


        /* =========================
           Back Link
           ========================= */

        .back-link {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            margin-bottom: 28px;

            color: var(--ink-soft);

            font-size: 12px;

            font-weight: 600;

            text-decoration: none;

            transition: color 0.15s ease;
        }


        .back-link:hover {

            color: var(--brass);
        }


        .back-arrow {

            font-size: 16px;
        }


        /* =========================
           Header
           ========================= */

        .page-header {

            margin-bottom: 28px;
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
           User Profile Card
           ========================= */

        .user-card {

            margin-bottom: 20px;

            padding: 24px;

            background: var(--paper-raised);

            border: 1px solid var(--line);

            border-radius: 12px;
        }


        .user-profile {

            display: flex;

            align-items: center;

            gap: 15px;

            margin-bottom: 24px;

            padding-bottom: 20px;

            border-bottom: 1px solid var(--line);
        }


        .user-avatar {

            width: 50px;

            height: 50px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background: var(--brass-soft);

            color: var(--brass);

            font-family: var(--font-display);

            font-size: 20px;

            font-weight: 600;

            text-transform: uppercase;
        }


        .user-name {

            margin-bottom: 4px;

            font-family: var(--font-display);

            font-size: 20px;

            font-weight: 600;
        }


        .user-email {

            color: var(--ink-faint);

            font-size: 12px;
        }


        /* =========================
           User Information
           ========================= */

        .info-grid {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 18px;
        }


        .info-label {

            margin-bottom: 6px;

            color: var(--ink-faint);

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 0.08em;

            text-transform: uppercase;
        }


        .info-value {

            font-size: 13px;

            font-weight: 600;
        }


        .role-value {

            display: inline-block;

            padding: 5px 9px;

            border-radius: 6px;

            background: var(--moss-soft);

            color: var(--moss);

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 0.04em;

            text-transform: uppercase;
        }


        /* =========================
           Action Card
           ========================= */

        .action-card {

            margin-bottom: 20px;

            padding: 22px;

            background: var(--paper-raised);

            border: 1px solid var(--line);

            border-radius: 12px;
        }


        .action-title {

            margin-bottom: 5px;

            font-family: var(--font-display);

            font-size: 19px;

            font-weight: 600;
        }


        .action-description {

            margin-bottom: 17px;

            color: var(--ink-soft);

            font-size: 12px;

            line-height: 1.5;
        }


        /* =========================
           Role Form
           ========================= */

        .role-form {

            display: flex;

            gap: 10px;
        }


        .role-select {

            flex: 1;

            height: 42px;

            padding: 0 12px;

            border: 1px solid var(--line);

            border-radius: 8px;

            background: var(--paper);

            color: var(--ink);

            font-family: var(--font-body);

            font-size: 13px;

            cursor: pointer;
        }


        .role-select:focus {

            outline: none;

            border-color: var(--brass);

            box-shadow: 0 0 0 3px var(--brass-soft);
        }


        .update-btn {

            height: 42px;

            padding: 0 17px;

            border: 1px solid var(--ink);

            border-radius: 8px;

            background: var(--ink);

            color: var(--paper);

            font-size: 12px;

            font-weight: 600;

            cursor: pointer;

            transition:
                background 0.15s ease,
                color 0.15s ease;
        }


        .update-btn:hover {

            background: var(--paper);

            color: var(--ink);
        }


        /* =========================
           Danger Zone
           ========================= */

        .danger-card {

            padding: 22px;

            background: var(--paper-raised);

            border: 1px solid #E4CFC8;

            border-radius: 12px;
        }


        .danger-title {

            margin-bottom: 5px;

            color: var(--danger);

            font-family: var(--font-display);

            font-size: 18px;

            font-weight: 600;
        }


        .danger-description {

            margin-bottom: 17px;

            color: var(--ink-soft);

            font-size: 12px;

            line-height: 1.5;
        }


        .delete-btn {

            height: 40px;

            padding: 0 15px;

            border: 1px solid #DDBDB4;

            border-radius: 8px;

            background: var(--danger-soft);

            color: var(--danger);

            font-family: var(--font-body);

            font-size: 12px;

            font-weight: 600;

            cursor: pointer;

            transition:
                background 0.15s ease,
                border-color 0.15s ease;
        }


        .delete-btn:hover {

            background: #EDD7D1;

            border-color: #D3AAA0;
        }


        /* =========================
           Mobile
           ========================= */

        @media (max-width: 767px) {

            .details-container {

                padding: 35px 16px;
            }


            .page-header h1 {

                font-size: 29px;
            }


            .user-card,
            .action-card,
            .danger-card {

                padding: 18px;
            }


            .info-grid {

                grid-template-columns: 1fr;
            }


            .role-form {

                flex-direction: column;
            }


            .update-btn {

                width: 100%;
            }
        }


        /* =========================
           Accessibility
           ========================= */

        .back-link:focus-visible,
        .role-select:focus-visible,
        .update-btn:focus-visible,
        .delete-btn:focus-visible {

            outline: 2px solid var(--brass);

            outline-offset: 2px;
        }


        @media (prefers-reduced-motion: reduce) {

            .user-details-page * {

                transition: none !important;
            }
        }

    </style>


    <div class="user-details-page">

        <div class="details-container">


            {{-- Back --}}

            <a
                href="{{ route('admin.users') }}"
                class="back-link"
            >
                <span class="back-arrow">←</span>
                Back to Users
            </a>


            {{-- Header --}}

            <div class="page-header">

                <div class="page-eyebrow">
                    Administration
                </div>

                <h1>
                    User Details
                </h1>

                <p>
                    View user information and manage workspace
                    access.
                </p>

            </div>


            {{-- User Information --}}

            <div class="user-card">

                <div class="user-profile">

                    <div class="user-avatar">
                        {{ substr($user->name, 0, 1) }}
                    </div>

                    <div>

                        <div class="user-name">
                            {{ $user->name }}
                        </div>

                        <div class="user-email">
                            {{ $user->email }}
                        </div>

                    </div>

                </div>


                <div class="info-grid">

                    <div>

                        <div class="info-label">
                            Name
                        </div>

                        <div class="info-value">
                            {{ $user->name }}
                        </div>

                    </div>


                    <div>

                        <div class="info-label">
                            Email
                        </div>

                        <div class="info-value">
                            {{ $user->email }}
                        </div>

                    </div>


                    <div>

                        <div class="info-label">
                            Current Role
                        </div>

                        <div class="role-value">
                            {{ $user->role }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Change Role --}}

            @can('update', $user)

                <div class="action-card">

                    <div class="action-title">
                        Change Role
                    </div>

                    <div class="action-description">
                        Update this user's role and workspace permissions.
                    </div>


                    <form
                        method="POST"
                        action="{{ route('admin.users.update-role', $user) }}"
                        class="role-form"
                    >

                        @csrf
                        @method('PUT')


                        <select
                            name="role"
                            class="role-select"
                        >

                            <option
                                value="user"
                                {{ $user->role === 'user' ? 'selected' : '' }}
                            >
                                User
                            </option>

                            <option
                                value="manager"
                                {{ $user->role === 'manager' ? 'selected' : '' }}
                            >
                                Manager
                            </option>

                            <option
                                value="admin"
                                {{ $user->role === 'admin' ? 'selected' : '' }}
                            >
                                Admin
                            </option>

                        </select>


                        <button
                            type="submit"
                            class="update-btn"
                        >
                            Update Role
                        </button>

                    </form>

                </div>

            @endcan


            {{-- Delete User --}}

            @can('delete', $user)

                <div class="danger-card">

                    <div class="danger-title">
                        Delete User
                    </div>

                    <div class="danger-description">
                        Permanently remove this user from the workspace.
                        This action cannot be undone.
                    </div>


                    <form
                        method="POST"
                        action="{{ route('admin.users.delete', $user) }}"
                        onsubmit="return confirm('Are you sure you want to delete this user?');"
                    >

                        @csrf
                        @method('DELETE')


                        <button
                            type="submit"
                            class="delete-btn"
                        >
                            Delete User
                        </button>

                    </form>

                </div>

            @endcan


        </div>

    </div>

</x-app-layout>