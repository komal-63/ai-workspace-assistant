<nav class="main-nav">

    <div class="nav-container">

        {{-- Logo / Brand --}}
        <a href="{{ route('dashboard') }}" class="nav-brand">
            AI Workspace
        </a>

        {{-- Desktop Navigation --}}
        <div class="nav-links">

            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('conversations.index') }}"
               class="nav-link {{ request()->routeIs('conversations.*', 'messages.*') ? 'active' : '' }}">
                Conversations
            </a>

            <a href="{{ route('documents.index') }}"
               class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                Documents
            </a>

            {{-- Admin Navigation --}}
            @if(Auth::user()->role === 'admin')

                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    Admin
                </a>

            @endif

        </div>

        {{-- Desktop User Menu --}}
        <div class="user-menu">

            <button type="button"
                    class="user-button"
                    onclick="toggleUserMenu()">

                <span>{{ Auth::user()->name }}</span>

                <span class="user-arrow" id="userArrow">
                    ▼
                </span>

            </button>

            <div class="user-dropdown" id="userDropdown">

                <a href="{{ route('profile.edit') }}">
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit">
                        Log Out
                    </button>
                </form>

            </div>

        </div>

        {{-- Mobile Menu Button --}}
        <button type="button"
                class="mobile-menu-button"
                onclick="toggleMobileMenu()"
                aria-label="Toggle navigation">

            <span></span>
            <span></span>
            <span></span>

        </button>

    </div>

    {{-- Mobile Navigation --}}
    <div class="mobile-menu" id="mobileMenu">

        <a href="{{ route('dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            Dashboard
        </a>

        <a href="{{ route('conversations.index') }}"
           class="{{ request()->routeIs('conversations.*', 'messages.*') ? 'active' : '' }}">
            Conversations
        </a>

        <a href="{{ route('documents.index') }}"
           class="{{ request()->routeIs('documents.*') ? 'active' : '' }}">
            Documents
        </a>

        @if(Auth::user()->role === 'admin')

            <a href="{{ route('admin.dashboard') }}"
               class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">
                Admin
            </a>

        @endif

        <div class="mobile-user-section">

            <div class="mobile-user-name">
                {{ Auth::user()->name }}
            </div>

            <a href="{{ route('profile.edit') }}">
                Profile
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit">
                    Log Out
                </button>
            </form>

        </div>

    </div>

</nav>


<style>

    /* =========================
       Design Tokens
       ========================= */

    .main-nav {

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

        background: var(--paper-raised);
        border-bottom: 1px solid var(--line);
        font-family: var(--font-body);
        color: var(--ink);

        position: relative;
        z-index: 100;
    }


    /* =========================
       Container
       ========================= */

    .nav-container {

        max-width: 1100px;
        height: 68px;
        margin: 0 auto;
        padding: 0 24px;

        display: flex;
        align-items: center;
    }


    /* =========================
       Brand
       ========================= */

    .nav-brand {

        color: var(--ink);
        font-family: var(--font-display);
        font-size: 20px;
        font-weight: 600;
        text-decoration: none;

        white-space: nowrap;
    }

    .nav-brand:hover {
        color: var(--brass);
    }


    /* =========================
       Navigation Links
       ========================= */

    .nav-links {

        display: flex;
        align-items: center;
        gap: 28px;

        margin-left: 48px;
    }

    .nav-link {

        position: relative;

        color: var(--ink-soft);
        font-size: 13px;
        font-weight: 500;

        text-decoration: none;

        padding: 25px 0 23px;

        transition: color 0.15s ease;
    }

    .nav-link:hover {
        color: var(--ink);
    }

    .nav-link.active {
        color: var(--ink);
        font-weight: 600;
    }

    .nav-link.active::after {

        content: "";

        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;

        height: 2px;

        background: var(--brass);
    }


    /* =========================
       User Menu
       ========================= */

    .user-menu {

        position: relative;
        margin-left: auto;
    }

    .user-button {

        border: none;
        background: transparent;

        display: flex;
        align-items: center;
        gap: 7px;

        padding: 8px 0;

        color: var(--ink-soft);

        font-family: var(--font-body);
        font-size: 13px;
        font-weight: 500;

        cursor: pointer;
    }

    .user-button:hover {
        color: var(--ink);
    }

    .user-arrow {

        font-size: 8px;

        transition: transform 0.15s ease;
    }

    .user-arrow.open {
        transform: rotate(180deg);
    }


    /* =========================
       User Dropdown
       ========================= */

    .user-dropdown {

        display: none;

        position: absolute;
        top: calc(100% + 10px);
        right: 0;

        width: 150px;

        padding: 6px;

        background: var(--paper-raised);
        border: 1px solid var(--line);
        border-radius: 9px;

        box-shadow: 0 8px 25px rgba(35, 36, 31, 0.08);
    }

    .user-dropdown.open {
        display: block;
    }

    .user-dropdown a,
    .user-dropdown button {

        display: block;

        width: 100%;

        padding: 9px 10px;

        border: none;
        border-radius: 6px;

        background: transparent;

        color: var(--ink-soft);

        font-family: var(--font-body);
        font-size: 12px;

        text-align: left;
        text-decoration: none;

        cursor: pointer;
    }

    .user-dropdown a:hover,
    .user-dropdown button:hover {

        background: var(--paper);
        color: var(--ink);
    }


    /* =========================
       Mobile
       ========================= */

    .mobile-menu-button {
        display: none;

        margin-left: auto;

        width: 40px;
        height: 40px;

        border: 1px solid var(--line);
        border-radius: 8px;

        background: var(--paper-raised);

        cursor: pointer;
    }

    .mobile-menu-button span {

        display: block;

        width: 18px;
        height: 1px;

        margin: 4px auto;

        background: var(--ink);
    }


    .mobile-menu {
        display: none;

        padding: 8px 16px 18px;

        background: var(--paper-raised);
        border-top: 1px solid var(--line);
    }

    .mobile-menu.open {
        display: block;
    }

    .mobile-menu > a {

        display: block;

        padding: 11px 10px;

        border-radius: 7px;

        color: var(--ink-soft);

        font-size: 13px;
        text-decoration: none;
    }

    .mobile-menu > a:hover,
    .mobile-menu > a.active {

        background: var(--paper);
        color: var(--ink);
    }

    .mobile-menu > a.active {
        font-weight: 600;
    }


    /* =========================
       Mobile User Section
       ========================= */

    .mobile-user-section {

        margin-top: 10px;
        padding-top: 12px;

        border-top: 1px solid var(--line);
    }

    .mobile-user-name {

        padding: 8px 10px;

        color: var(--ink);
        font-size: 13px;
        font-weight: 600;
    }

    .mobile-user-section a,
    .mobile-user-section button {

        display: block;

        width: 100%;

        padding: 10px;

        border: none;
        border-radius: 7px;

        background: transparent;

        color: var(--ink-soft);

        font-family: var(--font-body);
        font-size: 13px;

        text-align: left;
        text-decoration: none;

        cursor: pointer;
    }

    .mobile-user-section a:hover,
    .mobile-user-section button:hover {

        background: var(--paper);
        color: var(--ink);
    }


    /* =========================
       Mobile Breakpoint
       ========================= */

    @media (max-width: 767px) {

        .nav-container {
            height: 60px;
            padding: 0 16px;
        }

        .nav-brand {
            font-size: 19px;
        }

        .nav-links,
        .user-menu {
            display: none;
        }

        .mobile-menu-button {
            display: block;
        }
    }


    /* =========================
       Accessibility
       ========================= */

    .nav-brand:focus-visible,
    .nav-link:focus-visible,
    .user-button:focus-visible,
    .user-dropdown a:focus-visible,
    .user-dropdown button:focus-visible,
    .mobile-menu-button:focus-visible,
    .mobile-menu a:focus-visible,
    .mobile-menu button:focus-visible {

        outline: 2px solid var(--brass);
        outline-offset: 2px;
    }


    @media (prefers-reduced-motion: reduce) {

        .main-nav * {
            transition: none !important;
        }
    }

</style>


<script>

    function toggleUserMenu() {

        const dropdown = document.getElementById('userDropdown');
        const arrow = document.getElementById('userArrow');

        dropdown.classList.toggle('open');
        arrow.classList.toggle('open');
    }


    function toggleMobileMenu() {

        const menu = document.getElementById('mobileMenu');

        menu.classList.toggle('open');
    }


    // Close user dropdown when clicking outside

    document.addEventListener('click', function (event) {

        const userMenu = document.querySelector('.user-menu');

        if (!userMenu) {
            return;
        }

        if (!userMenu.contains(event.target)) {

            document
                .getElementById('userDropdown')
                .classList.remove('open');

            document
                .getElementById('userArrow')
                .classList.remove('open');
        }

    });

</script>