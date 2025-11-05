{{-- resources/views/errors/404.blade.php --}}
@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan | CV Arta Solusindo')

@section('content')
<section class="section py-5 text-center d-flex align-items-center" style="min-height: 80vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="mb-4">
                    <img
                        src="{{ asset('img/404.png') }}"
                        alt="404 Not Found"
                        class="img-fluid mx-auto d-block"
                        style="max-width: 100%; height: auto; width: clamp(250px, 80%, 500px);"
                    >
                </div>

                <h2 class="fw-bold mb-3">Halaman Tidak Ditemukan</h2>
                <p class="text-muted mb-4 px-3">
                    Maaf, halaman yang Anda cari tidak tersedia.
                </p>

                <a href="/" class="btn px-4 py-2" style="background-color:#b40404;color:#fff;">
                    <i class="bi bi-house-door me-1"></i> Kembali ke Home
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
