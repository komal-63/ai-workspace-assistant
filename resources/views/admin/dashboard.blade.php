<x-app-layout>

    <style>

        /* =========================
           Design Tokens
           ========================= */

        .admin-dashboard-page {

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

        .admin-container {

            max-width: 900px;

            margin: 0 auto;

            padding: 50px 24px;
        }


        /* =========================
           Header
           ========================= */

        .admin-header {

            margin-bottom: 34px;
        }


        .admin-eyebrow {

            margin-bottom: 8px;

            color: var(--brass);

            font-size: 11px;

            font-weight: 700;

            letter-spacing: 0.12em;

            text-transform: uppercase;
        }


        .admin-header h1 {

            margin: 0;

            font-family: var(--font-display);

            font-size: 34px;

            font-weight: 600;

            letter-spacing: -0.02em;
        }


        .admin-header p {

            max-width: 600px;

            margin: 8px 0 0;

            color: var(--ink-soft);

            font-size: 14px;

            line-height: 1.6;
        }


        /* =========================
           Section Header
           ========================= */

        .section-header {

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


        /* =========================
           Statistics Grid
           ========================= */

        .stats-grid {

            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 14px;

            margin-bottom: 34px;
        }


        .stat-card {

            padding: 20px;

            background: var(--paper-raised);

            border: 1px solid var(--line);

            border-radius: 12px;
        }


        .stat-label {

            margin-bottom: 10px;

            color: var(--ink-soft);

            font-size: 11px;

            font-weight: 600;
        }


        .stat-value {

            font-family: var(--font-display);

            font-size: 30px;

            font-weight: 600;

            line-height: 1;
        }


        /* =========================
           Stat Accents
           ========================= */

        .stat-card.total {

            border-top: 3px solid var(--brass);
        }


        .stat-card.admin {

            border-top: 3px solid var(--ink);
        }


        .stat-card.manager {

            border-top: 3px solid var(--moss);
        }


        .stat-card.user {

            border-top: 3px solid #B8A78C;
        }


        /* =========================
           Manage Users Card
           ========================= */

        .manage-card {

            display: flex;

            align-items: center;

            gap: 16px;

            padding: 20px;

            background: var(--paper-raised);

            border: 1px solid var(--line);

            border-radius: 12px;

            text-decoration: none;

            color: var(--ink);

            transition:
                background 0.15s ease,
                border-color 0.15s ease,
                transform 0.15s ease;
        }


        .manage-card:hover {

            background: var(--paper);

            border-color: #D9D0BF;

            transform: translateY(-2px);
        }


        .manage-icon {

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

            font-size: 16px;

            font-weight: 600;
        }


        .manage-content {

            flex: 1;
        }


        .manage-title {

            margin-bottom: 5px;

            font-family: var(--font-display);

            font-size: 18px;

            font-weight: 600;
        }


        .manage-description {

            color: var(--ink-soft);

            font-size: 12px;

            line-height: 1.5;
        }


        .manage-arrow {

            color: var(--ink-faint);

            font-size: 18px;

            transition:
                color 0.15s ease,
                transform 0.15s ease;
        }


        .manage-card:hover .manage-arrow {

            color: var(--brass);

            transform: translateX(3px);
        }


        /* =========================
           Mobile
           ========================= */

        @media (max-width: 767px) {

            .admin-container {

                padding: 35px 16px;
            }


            .admin-header h1 {

                font-size: 29px;
            }


            .stats-grid {

                grid-template-columns: 1fr 1fr;

                gap: 10px;
            }


            .stat-card {

                padding: 16px;
            }


            .stat-value {

                font-size: 26px;
            }


            .manage-card {

                padding: 16px;
            }
        }


        @media (max-width: 420px) {

            .stats-grid {

                grid-template-columns: 1fr;
            }
        }


        /* =========================
           Accessibility
           ========================= */

        .manage-card:focus-visible {

            outline: 2px solid var(--brass);

            outline-offset: 2px;
        }


        @media (prefers-reduced-motion: reduce) {

            .admin-dashboard-page * {

                transition: none !important;
            }
        }

    </style>


    <div class="admin-dashboard-page">

        <div class="admin-container">


            {{-- Page Header --}}

            <div class="admin-header">

                <div class="admin-eyebrow">
                    Administration
                </div>

                <h1>
                    Admin Dashboard
                </h1>

                <p>
                    Monitor your workspace and manage users
                    from one place.
                </p>

            </div>


            {{-- Statistics --}}

            <div class="section-header">

                <div class="section-title">
                    USER STATISTICS
                </div>

            </div>


            <div class="stats-grid">


                {{-- Total Users --}}

                <div class="stat-card total">

                    <div class="stat-label">
                        Total Users
                    </div>

                    <div class="stat-value">
                        {{ $totalUsers }}
                    </div>

                </div>


                {{-- Admins --}}

                <div class="stat-card admin">

                    <div class="stat-label">
                        Administrators
                    </div>

                    <div class="stat-value">
                        {{ $totalAdmins }}
                    </div>

                </div>


                {{-- Managers --}}

                <div class="stat-card manager">

                    <div class="stat-label">
                        Managers
                    </div>

                    <div class="stat-value">
                        {{ $totalManagers }}
                    </div>

                </div>


                {{-- Normal Users --}}

                <div class="stat-card user">

                    <div class="stat-label">
                        Normal Users
                    </div>

                    <div class="stat-value">
                        {{ $totalNormalUsers }}
                    </div>

                </div>


            </div>


            {{-- User Management --}}

            <div class="section-header">

                <div class="section-title">
                    USER MANAGEMENT
                </div>

            </div>


            <a
                href="{{ route('admin.users') }}"
                class="manage-card"
            >

                <div class="manage-icon">
                    U
                </div>

                <div class="manage-content">

                    <div class="manage-title">
                        Manage Users
                    </div>

                    <div class="manage-description">
                        View users, change roles and manage
                        workspace access.
                    </div>

                </div>

                <div class="manage-arrow">
                    →
                </div>

            </a>


        </div>

    </div>

</x-app-layout>