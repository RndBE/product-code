@extends('layouts.app')

@section('title', 'Verifikasi Produk')

@section('mini-hero')
<div class="text-center py-5 bg-light border-bottom">
    <h1 class="fw-bold mb-2">Verifikasi Keaslian Produk</h1>
    <p class="text-muted mb-0">Masukkan nomor seri (S/N) atau IMEI produk Anda untuk memeriksa keaslian, garansi, dan dokumen resmi.</p>
</div>
@endsection

@push('styles')
<style>
    /* card + form */
    .verify-card {
        border-radius: 14px;
        overflow: hidden;
    }
    .verify-hero {
        background: linear-gradient(90deg,#fff 0,#f8fafc 100%);
        padding: 28px;
    }

    /* input besar + custom */
    .input-lg {
        font-size:1.05rem;
        padding: .85rem 1rem .85rem 2.6rem;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        transition: all 0.25s ease-in-out;
    }
    .input-lg:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
    }

    /* input wrapper */
    .input-wrapper {
        position: relative;
    }
    .input-wrapper .icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 1rem;
    }

    /* tombol */
    .btn-verify {
        background: linear-gradient(90deg, #B40404 0%, #2E2E4D 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 10px;
        border-radius: 6px;
        transition: opacity 0.3s ease;
    }

    .btn-verify:hover:not(:disabled) {
        opacity: 0.9;
        color: #fff;
    }

    .btn-verify:disabled {
        background: #ccc !important;
        color: #666 !important;
        cursor: not-allowed;
    }

    /* responsive tweak */
    @media (max-width: 575px) {
        .btn-verify {
            width: 100%;
            margin-top: 10px;
        }
    }

    /* helper column */
    .help-card {
        border-radius: 12px;
        padding: 20px;
        background: linear-gradient(180deg,#fff,#fbfdff);
    }

    .help-step {
        display:flex;
        gap:14px;
        align-items:flex-start;
        margin-bottom:18px;
        position: relative;
    }

    /* Dot bulat */
    .help-step .dot {
        width:38px;
        height:38px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:700;
        color:#fff;
        font-size:1rem;
        box-shadow:0 3px 8px rgba(0,0,0,0.12);
    }

    /* Warna per step */
    .step-1 { background: linear-gradient(135deg,#14b8a6,#0f766e); } /* teal */
    .step-2 { background: linear-gradient(135deg,#3b82f6,#1e40af); } /* blue */
    .step-3 { background: linear-gradient(135deg,#f59e0b,#b45309); } /* optional extra */

    .help-step div:last-child {
        flex: 1;
    }

    .help-step .fw-semibold { font-size: .98rem; }
    .muted-small { color:#6b7280; font-size:.9rem; }

    .sn-badge {
        background:rgba(14,165,164,0.12);
        color:#065f60;
        padding:.25rem .6rem;
        border-radius:999px;
        font-weight:600;
        font-size:.85rem;
    }

    .product-card {
        border-radius:12px;
        padding:18px;
        background:#fff;
        box-shadow:0 8px 30px rgba(2,6,23,0.06);
    }

    /* responsive: stack columns */
    @media (max-width: 991px) {
        .help-card { margin-top:18px; }
    }
</style>

@endpush

@section('content')
<div class="container py-5">
    <div class="row gy-4">
        {{-- left: form + result --}}
        <div class="col-lg-7">
            <div class="card verify-card shadow-sm">
                <div class="verify-hero">
                    <h4 class="mb-2">Cek Serial Number</h4>
                    <p class="text-muted mb-3">Masukkan S/N untuk memastikan keaslian dan mengakses dokumen resmi produk.  hgv</p>

                    <form method="POST" action="{{ route('verify.store') }}" id="verifyForm">
                        @csrf
                        <div class="row g-2">
                            {{-- Input Serial Number --}}
                            <div class="col-12 mb-3">
                                <div class="input-wrapper">
                                    <i class="bi bi-upc-scan icon"></i>
                                    <input type="text"
                                        name="serial_number"
                                        id="serial_number"
                                        value="{{ old('serial_number', $serial_number ?? '') }}"
                                        class="form-control form-control-lg input-lg @error('serial_number') is-invalid @enderror"
                                        placeholder="Masukkan Serial Number dari label produk" required>
                                </div>
                                @error('serial_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Captcha --}}
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    {{-- Input captcha --}}
                                    <input type="text"
                                        name="captcha_code"
                                        id="captcha_code"
                                        class="form-control form-control-lg @error('captcha_code') is-invalid @enderror"
                                        placeholder="Masukkan kode verifikasi" required>

                                    {{-- Gambar captcha --}}
                                    <img src="{{ route('captcha.generate') }}" id="captchaImage" alt="captcha"
                                        class="rounded shadow-sm" style="height: 45px;">

                                    {{-- Tombol reload --}}
                                    <button type="button" class="btn btn-outline-secondary" id="reloadCaptcha">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>

                                @error('captcha_code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tombol --}}
                            <div class="col-12 d-grid">
                                <button class="btn btn-verify btn-lg" type="submit" id="verifyBtn" disabled>
                                    <i class="bi bi-check-circle me-1"></i> Verifikasi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- RESULT area --}}
                <div class="p-4">
                    @isset($result)
                        @if($result['status'] === 'valid')
                            <div class="product-card mb-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="flex:1;">
                                        <h5 class="mb-1">{{ $result['product']->name }}</h5>
                                        <div class="muted-small mb-2">Serial Number (S/N)</div>
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <div class="sn-badge">{{ $result['serial_number'] }}</div>
                                            @if(!empty($result['product']->slug))
                                                <a href="{{ route('user.manual', $result['product']->slug) }}" class="btn btn-sm btn-outline-primary">Lihat Dokumen</a>
                                            @endif
                                        </div>

                                        <p class="muted-small mb-2">Status: <span class="text-success fw-semibold">Terverifikasi</span></p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-danger rounded-3">
                                <h5 class="mb-1">❌ Serial Number Tidak Ditemukan</h5>
                                <p class="mb-0">Nomor seri <strong>{{ $result['serial_number'] }}</strong> tidak terdaftar. Periksa kembali atau hubungi support.</p>
                            </div>
                        @endif
                    @endisset
                </div>
            </div>
        </div>

        {{-- right: help / where to find S/N --}}
        <div class="col-lg-5">
            <div class="help-card shadow-sm">
                <h6 class="mb-3">Di mana menemukan Serial Number (S/N)?</h6>

                <div class="help-step">
                    <div class="dot step-1">1</div>
                    <div>
                        <div class="fw-semibold mb-1">Label pada Perangkat</div>
                        <div class="muted-small">
                            Lihat bagian belakang atau samping <strong>Data Logger</strong>.
                            Terdapat stiker putih dengan kode batang (barcode) dan nomor seri (S/N).
                            Gunakan kode angka/huruf yang tertera.
                        </div>
                    </div>
                </div>

                <div class="help-step">
                    <div class="dot step-2">2</div>
                    <div>
                        <div class="fw-semibold mb-1">Box Kemasan Produk</div>
                        <div class="muted-small">
                            Jika label pada perangkat tidak terbaca, periksa box kemasan.
                            Nomor seri biasanya tercetak pada stiker label di sisi box.
                        </div>
                    </div>
                </div>

                <hr>

                <div class="text-center mb-3">
                    <img src="{{ asset('img/Label.png') }}"
                        alt="Contoh Label Serial Number Data Logger"
                        class="img-fluid rounded border"
                        style="max-height:180px;">
                    <p class="muted-small mt-2">Contoh label serial number pada perangkat data logger.</p>
                </div>

                <h6 class="mb-2">Tips cepat</h6>
                <ul class="muted-small mb-3">
                    <li>Masukkan nomor seri lengkap, tanpa spasi tambahan.</li>
                    <li>Simpan foto label S/N untuk memudahkan saat klaim garansi.</li>
                    <li>Jika label hilang/rusak, hubungi tim support dengan bukti pembelian.</li>
                </ul>

                <div class="d-grid gap-2">
                    <a href="mailto:info@bejogja.com" class="btn btn-outline-secondary btn-sm">Hubungi Support</a>
                </div>
            </div>
        </div>

    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function toggleVerifyBtn() {
            let sn = document.getElementById('serial_number').value.trim();
            let captcha = document.getElementById('captcha_code').value.trim();
            document.getElementById('verifyBtn').disabled = !(sn && captcha);
        }

        // event listener input
        document.getElementById('serial_number').addEventListener('input', toggleVerifyBtn);
        document.getElementById('captcha_code').addEventListener('input', toggleVerifyBtn);

        // refresh captcha
        document.getElementById('reloadCaptcha').addEventListener('click', function() {
            document.getElementById('captchaImage').src = "{{ url('/captcha') }}" + "?" + Date.now();
        });

        // jalankan sekali saat load page
        toggleVerifyBtn();
    });
</script>
@endpush
@endsection
