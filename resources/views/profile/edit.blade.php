<x-app-layout>
    <div class="profile-settings">
        <div class="profile-page">
            <header class="profile-intro">
                <div class="profile-avatar" aria-hidden="true">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="profile-intro-copy">
                    <p class="profile-kicker">Account settings</p>
                    <h1>Profile</h1>
                    <p>Manage your personal details, sign-in security, and account preferences.</p>
                </div>
                <div class="profile-identity">
                    <span class="identity-label">Signed in as</span>
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->email }}</span>
                </div>
            </header>

            <div class="profile-layout">
                <aside class="profile-aside" aria-label="Profile sections">
                    <p class="aside-heading">Your account</p>
                    <nav class="profile-section-nav">
                        <a href="#profile-information"><span class="nav-mark">01</span>Personal details</a>
                        <a href="#profile-password"><span class="nav-mark">02</span>Password &amp; security</a>
                        <a href="#profile-delete"><span class="nav-mark">03</span>Delete account</a>
                    </nav>
                    <div class="privacy-note">
                        <span class="privacy-mark" aria-hidden="true">&#9670;</span>
                        <p>Your workspace is private to your account.</p>
                    </div>
                </aside>

                <main class="profile-content">
                    <section class="profile-section" id="profile-information">
                        @include('profile.partials.update-profile-information-form')
                    </section>

                    <section class="profile-section" id="profile-password">
                        @include('profile.partials.update-password-form')
                    </section>

                    <section class="profile-section profile-danger-section" id="profile-delete">
                        @include('profile.partials.delete-user-form')
                    </section>
                </main>
            </div>
        </div>
    </div>

    <style>
        .profile-settings {
            --profile-paper: #faf7f1;
            --profile-white: #fff;
            --profile-ink: #23241f;
            --profile-muted: #706f68;
            --profile-line: #e7e1d3;
            --profile-brass: #9c6b30;
            min-height: calc(100vh - 68px);
            padding: 44px 24px 72px;
            background: var(--profile-paper);
            color: var(--profile-ink);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .profile-page {
            max-width: 1100px;
            margin: 0 auto;
        }

        .profile-intro {
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr) minmax(200px, auto);
            align-items: center;
            gap: 20px;
            padding: 0 0 32px;
            border-bottom: 1px solid var(--profile-line);
        }

        .profile-avatar {
            display: grid;
            place-items: center;
            width: 64px;
            aspect-ratio: 1;
            border-radius: 18px;
            background: #e9dfcb;
            color: var(--profile-brass);
            font-family: 'Fraunces', Georgia, serif;
            font-size: 27px;
        }

        .profile-kicker, .aside-heading, .identity-label {
            margin: 0;
            color: var(--profile-brass);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .profile-intro h1 {
            margin: 4px 0 5px;
            font-family: 'Fraunces', Georgia, serif;
            font-size: 38px;
            font-weight: 500;
            line-height: 1.1;
            color: var(--profile-ink);
        }

        .profile-intro-copy > p:last-child {
            margin: 0;
            color: var(--profile-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .profile-identity {
            display: grid;
            gap: 4px;
            min-width: 210px;
            padding-left: 24px;
            border-left: 1px solid var(--profile-line);
            font-size: 13px;
        }

        .profile-identity strong {
            font-size: 14px;
        }

        .profile-identity > span:last-child {
            color: var(--profile-muted);
            overflow-wrap: anywhere;
        }

        .identity-label {
            font-size: 10px;
        }

        .profile-layout {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr);
            gap: clamp(32px, 6vw, 76px);
            padding-top: 32px;
        }

        .profile-aside {
            align-self: start;
            position: sticky;
            top: 92px;
        }

        .aside-heading {
            margin-bottom: 13px;
            color: var(--profile-muted);
        }

        .profile-section-nav {
            display: grid;
            gap: 4px;
        }

        .profile-section-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 42px;
            padding: 8px 10px;
            border-radius: 7px;
            color: var(--profile-ink);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .profile-section-nav a:hover,
        .profile-section-nav a:focus-visible {
            background: #eee7da;
            outline: none;
        }

        .nav-mark {
            color: var(--profile-brass);
            font-size: 10px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .privacy-note {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px solid var(--profile-line);
            color: var(--profile-muted);
        }

        .privacy-mark {
            color: var(--profile-brass);
            font-size: 11px;
            line-height: 1.7;
        }

        .privacy-note p {
            margin: 0;
            font-size: 12px;
            line-height: 1.6;
        }

        .profile-content {
            min-width: 0;
        }

        .profile-section {
            padding: 0 0 34px;
            margin-bottom: 34px;
            border-bottom: 1px solid var(--profile-line);
            scroll-margin-top: 96px;
        }

        .profile-section:last-child {
            margin-bottom: 0;
            border-bottom: 0;
        }

        .profile-section header h2 {
            color: var(--profile-ink) !important;
            font-family: 'Fraunces', Georgia, serif;
            font-size: 23px;
            font-weight: 500;
        }

        .profile-section header p,
        .profile-section p.text-sm {
            color: var(--profile-muted) !important;
            line-height: 1.65;
        }

        .profile-danger-section header h2 {
            color: #9f3c35 !important;
        }

        @media (max-width: 760px) {
            .profile-settings {
                padding: 30px 18px 48px;
            }

            .profile-intro {
                grid-template-columns: 54px minmax(0, 1fr);
                gap: 14px;
            }

            .profile-avatar {
                width: 54px;
                border-radius: 15px;
            }

            .profile-intro h1 {
                font-size: 32px;
            }

            .profile-identity {
                grid-column: 1 / -1;
                min-width: 0;
                padding: 16px 0 0;
                border-left: 0;
                border-top: 1px solid var(--profile-line);
            }

            .profile-layout {
                grid-template-columns: 1fr;
                gap: 30px;
                padding-top: 24px;
            }

            .profile-aside {
                position: static;
            }

            .profile-section-nav {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 6px;
            }

            .profile-section-nav a {
                align-items: flex-start;
                flex-direction: column;
                gap: 5px;
                padding: 10px;
                background: rgba(255,255,255,0.55);
                font-size: 12px;
            }

            .privacy-note {
                margin-top: 16px;
                padding-top: 12px;
            }
        }

        @media (max-width: 430px) {
            .profile-section-nav {
                grid-template-columns: 1fr;
            }

            .profile-section-nav a {
                align-items: center;
                flex-direction: row;
            }
        }
    </style>
</x-app-layout>
