<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Product Manual') — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('img/title.ico') }}">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@300;400;500;600;700&display=swap" rel="stylesheet">



    <style>
        :root{
            --brand:#0f172a;      /* deep navy */
            --accent:#0ea5a4;     /* teal */
            --muted:#6b7280;
            --card:#ffffff;
        }
        * {
            font-family: 'Lexend Deca', sans-serif !important;
        }
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
        }

        body, p, span, label, input, button {
            font-weight: 400;
        }

        body{
            color: #111827;
            background: #f6f7fb;

            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main{
            flex: 1;
            padding-top: 70px;
        }


        /* Header */
        .site-header {
            background: linear-gradient(90deg, #B40404 0%, #2E2E4D 100%);
            color: #fff;
            padding: 14px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
        }
        .site-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
            font-size: 1.125rem;
        }

        .site-header a {
            text-decoration: none !important;
        }

        /* Modern Offcanvas */
        .modern-nav {
            background: #fff;
            border-radius: 0 14px 14px 0;
            overflow: hidden;
            box-shadow: 4px 0 20px rgba(0,0,0,0.12);
        }

        .modern-nav .offcanvas-header {
            padding: 0.6rem;
        }

        .modern-nav .offcanvas-title {
            font-weight: 600;
        }

        /* Menu link */
        .modern-nav .nav-link {
            font-size: 1rem;
            font-weight: 500;
            color: #374151;
            border-left: 4px solid transparent;
            transition: all .25s ease;
        }

        .modern-nav .nav-link:hover {
            background: #f9fafb;
            border-left-color: #2E2E4D;
            color: #111827;
        }

        .modern-nav .nav-link i {
            font-size: 1.2rem;
            color: #6b7280;
            transition: color .25s ease;
        }

        .modern-nav .nav-link:hover i {
            color: #2E2E4D;
        }

        /* Active state */
        .modern-nav .nav-link.active {
            background: #f1f5f9;
            border-left-color: #B40404;
            color: #B40404;
            font-weight: 600;
        }

        .modern-nav .nav-link.active i {
            color: #B40404;
        }


        /* Footer */
        .site-footer{
            background: #2E2E4D;
            color: #cbd5e1;
            padding: 48px 0;
            margin-top: 64px;
        }
        .site-footer a{ color: #9fb6b5; text-decoration: none; }
        .site-footer a:hover{ color: #fff; text-decoration: underline; }

        /* card */
        .glass-card{
            background: var(--card);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(13,18,30,0.06);
            padding: 24px;
        }

        /* small helper */
        .muted{ color: var(--muted); }

        /* responsive iframe / preview */
        .pdf-frame{
            border-radius: 10px;
            border: 1px solid rgba(15,23,42,0.06);
            min-height: 520px;
            width: 100%;
        }

        /* article card */
        .article-card{
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            transition: transform .22s ease, box-shadow .22s ease;
            box-shadow: 0 6px 18px rgba(15,23,42,0.06);
            height: 100%;
        }
        .article-card:hover{
            transform: translateY(-6px);
            box-shadow: 0 14px 30px rgba(15,23,42,0.08);
        }

        /* share */
        .share-btn{ background: transparent; border:1px solid rgba(15,23,42,0.06); border-radius:8px; padding:6px 10px; }
    </style>

    @stack('styles')
</head>
<body>

    {{-- HEADER --}}
    <header class="site-header">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="d-flex align-items-center text-white text-decoration-none">
                <div class="me-3">
                    {{-- simple logo mark --}}
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" aria-hidden>
                        <rect width="24" height="24" rx="6" fill="#ffffff" opacity="0.08"></rect>
                        <path d="M6 12h12M6 16h8" stroke="#fff" stroke-width="1.5" stroke-linecap="round"></path>
                    </svg>
                </div>
                <div>
                    <div class="site-brand">Product Manual</div>
                    <div style="font-size:.76rem; opacity:.85; margin-top:-4px;">Document & Support</div>
                </div>
            </a>

            <nav class="d-none d-md-flex align-items-center gap-3">
                <a href="{{ url('/') }}" class="text-white">Home</a>

                @auth
                    {{-- Dropdown user --}}
                    <div class="dropdown">
                        <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                        href="#"
                        id="userDropdownDesktop"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        role="button">
                            {{-- small avatar circle with initials --}}
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                style="width:36px; height:36px; background: rgba(255,255,255,0.12); font-weight:700;">
                                {{ strtoupper(substr(Auth::user()->name,0,1) ?: 'U') }}
                            </div>
                            <div class="text-start">
                                <div style="line-height:1;">{{ Auth::user()->name }}</div>
                                <small class="d-block text-white-50" style="font-size:.75rem;">{{ Auth::user()->email }}</small>
                            </div>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdownDesktop">
                            <li class="px-3 py-2">
                                <div class="small text-muted">{{ Auth::user()->email }}</div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            {{-- optional: link to admin area --}}
                            <li>
                                <form action="{{ route('admin.logout') }}" method="POST" class="d-inline w-100">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    {{-- hide login button when already on admin login page --}}
                    @if (!Request::is('admin/login'))
                        <a href="{{ route('admin.login') }}" class="btn btn-sm btn-light">Login</a>
                    @endif
                @endauth
            </nav>

            {{-- mobile toggle --}}
            <div class="d-md-none">
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                    Menu
                </button>
            </div>
        </div>
    </header>

    {{-- mobile offcanvas --}}
    <div class="offcanvas offcanvas-start modern-nav" tabindex="-1" id="mobileNav">
        <div class="offcanvas-header border-0 text-white">
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 rounded bg-white bg-opacity-10">
                    <i class="bi bi-grid fs-4"></i>
                </div>
                <h5 class="offcanvas-title mb-0">Product Manual</h5>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-0">
            <nav class="nav flex-column">
                <a href="{{ url('/') }}" class="nav-link px-4 py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-house-door"></i> Home
                </a>

                @auth
                    <div class="px-4 py-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width:40px; height:40px; background: rgba(0,0,0,0.05); font-weight:700;">
                                {{ strtoupper(substr(Auth::user()->name,0,1) ?: 'U') }}
                            </div>
                            <div>
                                <div style="font-weight:600;">{{ Auth::user()->name }}</div>
                                <small class="text-muted">{{ Auth::user()->email }}</small>
                            </div>
                        </div>

                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light w-100">Logout</button>
                        </form>
                    </div>
                @else
                    {{-- hide login link when on admin/login --}}
                    @if (!Request::is('admin/login'))
                        <a href="{{ route('admin.login') }}" class="nav-link px-4 py-3 d-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    @endif
                @endauth
            </nav>
        </div>
    </div>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="site-footer">
        <div class="container">
            <div class="text-center muted" style="font-size:.9rem;color:#fff;">
                &copy; {{ date('Y') }} Product Manual. All rights reserved.
            </div>
        </div>
    </footer>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    @stack('scripts')
</body>
</html>
