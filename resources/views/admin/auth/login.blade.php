@extends('layouts.appAdmin')

@section('content')
@push('styles')
<style>
    :root{
        --brand-1: #B40404;   /* merah */
        --brand-2: #2E2E4D;   /* navy */
        --glass: rgba(255,255,255,0.9);
    }

    body { background: linear-gradient(180deg, #f4f6f8 0%, #eef2f6 100%); }

    .login-wrap {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .login-card {
        width: 100%;
        max-width: 420px;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(19, 24, 41, 0.12);
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(255,255,255,0.94));
        border: 1px solid rgba(46,46,77,0.05);
    }

    .login-top {
        padding: 28px;
        background: linear-gradient(90deg, var(--brand-1) 0%, var(--brand-2) 100%);
        color: #fff;
    }

    .login-top .logo {
        width: 56px;
        height: 56px;
        display: inline-grid;
        place-items: center;
        border-radius: 12px;
        background: rgba(255,255,255,0.08);
        margin-bottom: 8px;
    }

    .login-top h4 {
        margin: 0;
        font-weight: 700;
        letter-spacing: -0.2px;
    }

    .login-top p { margin: 0; opacity: .95; font-size: .92rem; }

    .login-body {
        padding: 26px;
    }

    .form-label { font-weight: 600; color: #1f2937; }

    .input-group .input-group-text {
        background: transparent;
        border-right: 0;
        color: #6b7280;
    }

    .form-control {
        border-left: 0;
        border-radius: 0.625rem;
        border: 1px solid #e6e9ef;
        padding: .8rem .85rem;
        transition: box-shadow .18s ease, border-color .18s ease;
    }

    .form-control:focus {
        border-color: var(--brand-2);
        box-shadow: 0 6px 18px rgba(46,46,77,0.08);
        outline: none;
    }

    .btn-brand {
        background: linear-gradient(90deg, var(--brand-1) 0%, var(--brand-2) 100%);
        border: 0;
        color: #fff;
        padding: .65rem .9rem;
        font-weight: 700;
        border-radius: .6rem;
        box-shadow: 0 8px 24px rgba(180,4,4,0.12);
        transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease;
    }

    .btn-brand:hover { transform: translateY(-2px); opacity: .98; box-shadow: 0 12px 36px rgba(46,46,77,0.14); }

    .login-footer { text-align: center; padding: 18px; font-size: .9rem; color: #6b7280; }

    /* small helper */
    .muted-small { font-size: .92rem; color: #6b7280; }

    @media (max-width: 480px) {
        .login-top { padding: 18px; }
        .login-body { padding: 18px; }
    }
</style>
@endpush

<div class="login-wrap">
    <div class="login-card">
        <div class="login-top d-flex align-items-center gap-3">
            <div class="px-3 py-3">
                <div class="logo">
                    {{-- simple svg mark --}}
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden>
                        <rect width="24" height="24" rx="6" fill="#fff" opacity="0.08"></rect>
                        <path d="M6 12h12M6 16h8" stroke="#fff" stroke-width="1.5" stroke-linecap="round"></path>
                    </svg>
                </div>
            </div>

            <div class="flex-grow-1">
                <h4>Admin Panel</h4>
                <p class="mb-0">Masuk untuk mengelola dokumentasi produk</p>
            </div>
        </div>

        <div class="login-body">
            @if(session('error') || $errors->any() || session('retry_after'))
                <div class="alert alert-danger">
                    {{-- pesan error biasa --}}
                    @if(session('error'))
                        {{ session('error') }}
                    @elseif($errors->any())
                        {{ $errors->first() }}
                    @endif

                    {{-- countdown kalau ada retry --}}
                    @if(session('retry_after'))
                        <div>
                            <span id="countdown"></span>
                        </div>

                        <script>
                            let retryAfter = {{ session('retry_after') }};
                            let countdownEl = document.getElementById('countdown');

                            function updateCountdown() {
                                let minutes = Math.floor(retryAfter / 60);
                                let seconds = retryAfter % 60;
                                countdownEl.textContent =
                                    "Terlalu banyak percobaan login. Coba lagi dalam "
                                    + minutes + " menit "
                                    + (seconds < 10 ? "0" : "") + seconds + " detik.";
                            }

                            updateCountdown();

                            let interval = setInterval(() => {
                                if (retryAfter > 0) {
                                    retryAfter--;
                                    updateCountdown();
                                } else {
                                    clearInterval(interval);
                                }
                            }, 1000);
                        </script>
                    @endif
                </div>
            @endif


            <form method="POST" action="{{ route('admin.login.submit') }}" autocomplete="off">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input id="email" type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus placeholder="you@example.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input id="password" type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required placeholder="••••••••">
                    </div>
                </div>

                <div class="mb-3 d-grid">
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
