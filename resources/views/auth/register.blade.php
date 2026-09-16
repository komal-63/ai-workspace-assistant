<x-guest-layout>

    <style>

        .auth-page {

            --paper: #FAF7F1;
            --paper-raised: #FFFFFF;
            --ink: #23241F;
            --ink-soft: #6B6A63;
            --ink-faint: #A6A399;
            --line: #E7E1D3;

            --brass: #9C6B30;
            --brass-soft: #F1E6D2;

            --font-display: 'Fraunces', Georgia, serif;
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 40px 20px;

            background: var(--paper);

            color: var(--ink);

            font-family: var(--font-body);
        }


        .auth-container {

            width: 100%;

            max-width: 420px;
        }


        .brand {

            margin-bottom: 30px;

            text-align: center;
        }


        .brand-eyebrow {

            margin-bottom: 8px;

            color: var(--brass);

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 0.14em;

            text-transform: uppercase;
        }


        .brand-title {

            margin: 0;

            font-family: var(--font-display);

            font-size: 32px;

            font-weight: 600;

            letter-spacing: -0.02em;
        }


        .brand-description {

            max-width: 340px;

            margin: 9px auto 0;

            color: var(--ink-soft);

            font-size: 13px;

            line-height: 1.6;
        }


        .auth-card {

            padding: 28px;

            background: var(--paper-raised);

            border: 1px solid var(--line);

            border-radius: 14px;

            box-shadow: 0 10px 30px rgba(35, 36, 31, 0.05);
        }


        .auth-heading {

            margin-bottom: 22px;
        }


        .auth-heading h2 {

            margin: 0;

            font-family: var(--font-display);

            font-size: 22px;

            font-weight: 600;
        }


        .auth-heading p {

            margin: 5px 0 0;

            color: var(--ink-soft);

            font-size: 12px;
        }


        .form-group {

            margin-bottom: 17px;
        }


        .form-label {

            display: block;

            margin-bottom: 7px;

            color: var(--ink-soft);

            font-size: 11px;

            font-weight: 600;
        }


        .form-input {

            width: 100%;

            box-sizing: border-box;

            height: 44px;

            padding: 10px 42px 10px 13px;

            border: 1px solid var(--line);

            border-radius: 8px;

            background: var(--paper);

            color: var(--ink);

            font-family: var(--font-body);

            font-size: 13px;
        }

        .password-input-wrap {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--ink-soft);
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
        }

        .password-toggle:hover,
        .password-toggle:focus-visible {
            background: var(--brass-soft);
            color: var(--ink);
            outline: none;
        }

        .password-toggle svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .password-toggle[aria-pressed="true"] {
            color: var(--brass);
        }


        .form-input:focus {

            outline: none;

            border-color: var(--brass);

            box-shadow: 0 0 0 3px var(--brass-soft);

            background: var(--paper-raised);
        }


        .error-message {

            margin-top: 6px;

            color: #9A4A3A;

            font-size: 11px;
        }


        .auth-actions {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 12px;

            margin-top: 22px;
        }


        .login-link {

            color: var(--ink-soft);

            font-size: 11px;

            text-decoration: none;
        }


        .login-link:hover {

            color: var(--brass);
        }


        .submit-btn {

            height: 42px;

            padding: 0 18px;

            border: 1px solid var(--ink);

            border-radius: 8px;

            background: var(--ink);

            color: var(--paper);

            font-family: var(--font-body);

            font-size: 12px;

            font-weight: 600;

            cursor: pointer;

            transition:
                background 0.15s ease,
                color 0.15s ease;
        }


        .submit-btn:hover {

            background: var(--paper);

            color: var(--ink);
        }


        .auth-footer {

            margin-top: 20px;

            text-align: center;

            color: var(--ink-faint);

            font-size: 11px;
        }


        .auth-footer a {

            color: var(--brass);

            font-weight: 600;

            text-decoration: none;
        }


        .auth-footer a:hover {

            text-decoration: underline;
        }


        @media (max-width: 480px) {

            .auth-page {

                padding: 25px 16px;
            }

            .brand-title {

                font-size: 28px;
            }

            .auth-card {

                padding: 22px;
            }

            .auth-actions {

                align-items: stretch;

                flex-direction: column-reverse;
            }

            .submit-btn {

                width: 100%;
            }

        }

    </style>


    <div class="auth-page">

        <div class="auth-container">


            {{-- Brand --}}

            <div class="brand">

                <div class="brand-eyebrow">
                    AI Workspace
                </div>

                <h1 class="brand-title">
                    AI Workspace Assistant
                </h1>

                <p class="brand-description">
                    Create your workspace account and start
                    working with your documents and AI conversations.
                </p>

            </div>


            {{-- Register Card --}}

            <div class="auth-card">

                <div class="auth-heading">

                    <h2>
                        Create your account
                    </h2>

                    <p>
                        Join AI Workspace Assistant.
                    </p>

                </div>


                <form
                    method="POST"
                    action="{{ route('register') }}"
                >

                    @csrf


                    {{-- Name --}}

                    <div class="form-group">

                        <label
                            for="name"
                            class="form-label"
                        >
                            Name
                        </label>

                        <input
                            id="name"
                            class="form-input"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                        >

                        @error('name')

                            <div class="error-message">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- Email --}}

                    <div class="form-group">

                        <label
                            for="email"
                            class="form-label"
                        >
                            Email
                        </label>

                        <input
                            id="email"
                            class="form-input"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                        >

                        @error('email')

                            <div class="error-message">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- Password --}}

                    <div class="form-group">

                        <label
                            for="password"
                            class="form-label"
                        >
                            Password
                        </label>

                        <div class="password-input-wrap">
                            <input
                                id="password"
                                class="form-input"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                            >
                            <button
                                type="button"
                                class="password-toggle"
                                data-password-toggle="password"
                                aria-label="Show password"
                                aria-pressed="false"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>

                        @error('password')

                            <div class="error-message">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- Confirm Password --}}

                    <div class="form-group">

                        <label
                            for="password_confirmation"
                            class="form-label"
                        >
                            Confirm Password
                        </label>

                        <div class="password-input-wrap">
                            <input
                                id="password_confirmation"
                                class="form-input"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                            >
                            <button
                                type="button"
                                class="password-toggle"
                                data-password-toggle="password_confirmation"
                                aria-label="Show confirm password"
                                aria-pressed="false"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>

                        @error('password_confirmation')

                            <div class="error-message">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- Actions --}}

                    <div class="auth-actions">

                        <a
                            class="login-link"
                            href="{{ route('login') }}"
                        >
                            Already have an account?
                        </a>


                        <button
                            type="submit"
                            class="submit-btn"
                        >
                            Create account
                        </button>

                    </div>

                </form>


                <div class="auth-footer">

                    AI Workspace Assistant

                </div>

            </div>


        </div>

    </div>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(function (toggleButton) {
            const targetId = toggleButton.dataset.passwordToggle;
            const targetField = document.getElementById(targetId);

            if (!targetField) {
                return;
            }

            toggleButton.addEventListener('click', function () {
                const isHidden = targetField.type === 'password';
                targetField.type = isHidden ? 'text' : 'password';
                toggleButton.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                toggleButton.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                toggleButton.innerHTML = isHidden ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"></path><path d="M10.58 10.58A2 2 0 0 0 13.42 13.42"></path><path d="M9.88 5.08A10.23 10.23 0 0 1 12 5c6.5 0 10 7 10 7a16.84 16.84 0 0 1-4.24 5.24"></path><path d="M5.42 6.42A17.48 17.48 0 0 0 2 12s3.5 7 10 7a9.77 9.77 0 0 0 4.72-1.28"></path></svg>' : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            });
        });
    </script>

</x-guest-layout>