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

            padding: 10px 13px;

            border: 1px solid var(--line);

            border-radius: 8px;

            background: var(--paper);

            color: var(--ink);

            font-family: var(--font-body);

            font-size: 13px;
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


        .remember-row {

            display: flex;

            align-items: center;

            margin-top: 4px;

            margin-bottom: 20px;
        }


        .remember-row input {

            accent-color: var(--brass);
        }


        .remember-row label {

            margin-left: 8px;

            color: var(--ink-soft);

            font-size: 12px;
        }


        .auth-actions {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 12px;
        }


        .forgot-link {

            color: var(--ink-soft);

            font-size: 11px;

            text-decoration: none;
        }


        .forgot-link:hover {

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
                    Your document-grounded AI workspace for
                    conversations, knowledge and productivity.
                </p>

            </div>


            {{-- Login Card --}}

            <div class="auth-card">

                <div class="auth-heading">

                    <h2>
                        Welcome back
                    </h2>

                    <p>
                        Sign in to continue to your workspace.
                    </p>

                </div>


                <x-auth-session-status
                    class="mb-4"
                    :status="session('status')"
                />


                <form
                    method="POST"
                    action="{{ route('login') }}"
                >

                    @csrf


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
                            autofocus
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

                        <input
                            id="password"
                            class="form-input"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                        >

                        @error('password')

                            <div class="error-message">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- Remember Me --}}

                    <div class="remember-row">

                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                        >

                        <label for="remember_me">
                            Remember me
                        </label>

                    </div>


                    {{-- Actions --}}

                    <div class="auth-actions">

                        @if (Route::has('password.request'))

                            <a
                                class="forgot-link"
                                href="{{ route('password.request') }}"
                            >
                                Forgot your password?
                            </a>

                        @endif


                        <button
                            type="submit"
                            class="submit-btn"
                        >
                            Sign in
                        </button>

                    </div>

                </form>


                <div class="auth-footer">

                    Don't have an account?

                    <a href="{{ route('register') }}">
                        Create one
                    </a>

                </div>

            </div>


        </div>

    </div>

</x-guest-layout>