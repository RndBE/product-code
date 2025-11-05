@extends('layouts.app')

@section('title', 'Situs Sedang Pemeliharaan | CV Arta Solusindo')

@section('content')
<section class="section py-5 text-center d-flex align-items-center" style="min-height: 80vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <img src="{{ asset('img/503.png') }}" alt="Maintenance"
                    class="img-fluid mx-auto d-block mb-4"
                    style="max-width: 100%; height: auto; width: clamp(250px, 80%, 450px);">
                <h2 class="fw-bold mb-3">Situs Sedang Dalam Pemeliharaan</h2>
                <p class="text-muted mb-4 px-3">
                    Kami sedang melakukan pembaruan sistem untuk meningkatkan layanan. Silakan kembali lagi nanti.
                </p>
                <button type="button" onclick="location.reload()"
                    class="btn px-4 py-2"
                    style="background-color:#b40404; color:#fff;">
                    <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
                </button>
            </div>
        </div>
    </div>
</section>
@endsection
