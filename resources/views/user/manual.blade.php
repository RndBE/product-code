{{-- bagian atas file tetap sama --}}
@extends('layouts.app')

@section('title', $product->name . ' — Dokumentasi Produk')

@push('styles')
<style>
    /* Sidebar khusus */
    .sidebar-section .doc-badge{ font-size:.78rem; padding:.25rem .5rem; border-radius:.5rem; }
    .doc-available{ background: rgba(16,185,129,0.12); color:#059669; border:1px solid rgba(16,185,129,0.12); }
    .doc-soon{ background: rgba(250,204,21,0.08); color:#b45309; border:1px solid rgba(250,204,21,0.08); }

    .app-btn {
        display:flex;
        align-items:center;
        gap:.6rem;
        justify-content:center;
        border-radius:8px;
        padding:.5rem .65rem;
        font-weight:600;
        text-decoration:none;
    }
    .app-btn svg{ width:20px; height:20px; opacity:.95; }

    .meta-row { display:flex; justify-content:space-between; gap:12px; align-items:center; margin-bottom:.5rem; }
    .meta-key { color:var(--muted); font-size:.9rem; }
    .meta-value { font-weight:600; font-size:.95rem; }

    .support-small { font-size:.9rem; color:var(--muted); }
</style>
@endpush

@section('content')
<div class="row gy-3 pt-4">
    {{-- main --}}
    <div class="col-lg-8">
        {{-- dokumentasi section (tetap seperti sebelum) --}}
        <section class="glass-card p-4">
            <h2 class="mb-3">Dokumentasi Resmi</h2>
            {{-- konten manual dari CKEditor --}}
            {{-- @if($product->content)
                    <div class="ck-content">
                        {!! $product->content !!}
                    </div>
            @endif --}}
            <section>
                <p class="mb-3">
                    Serial number <span class="fw-bold text-success">{{ $product->serial_number }}</span>
                    berhasil diverifikasi pada sistem kami. Produk ini terdaftar secara resmi dengan nama
                    <span class="fw-bold">{{ $product->name }}</span>, sehingga keaslian dan legalitasnya
                    dapat dipastikan.
                </p>

                <p class="mb-3">
                    Setiap unit telah melewati proses produksi dan <strong>Quality Control (QC)</strong>
                    yang ketat untuk menjamin mutu, performa, serta keamanan pengguna. Dengan demikian,
                    Anda dapat menggunakan produk ini dengan penuh keyakinan.
                </p>

                <p class="mb-3">
                    Seluruh dokumen dapat diakses secara digital langsung melalui platform ini.
                    Dokumen-dokumen tersebut dapat menjadi panduan instalasi, pemeliharaan, maupun sebagai
                    bukti administrasi resmi produk Anda.
                </p>

                <p class="mb-0">
                    Apabila Anda membutuhkan bantuan lebih lanjut, tim layanan pelanggan kami siap membantu
                    kapan saja. Silakan hubungi kami untuk dukungan terkait produk maupun dokumen yang tersedia.
                </p>
            </section>

            <ul class="nav nav-tabs mt-3" id="docTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button">📘 User Manual</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="qc-tab" data-bs-toggle="tab" data-bs-target="#qc" type="button">🏅 Sertifikat QC</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="warranty-tab" data-bs-toggle="tab" data-bs-target="#warranty" type="button">📑 Garansi</button>
                </li>
            </ul>

            <div class="tab-content border p-3 bg-white rounded-bottom" id="docTabsContent">
                <div class="tab-pane fade show active" id="manual" role="tabpanel">
                    @if($product->manual_file)
                        @php
                            $manualUrl = 'https://stesy.beacontelemetry.com/product/manual/' . $product->manual_file;
                        @endphp

                        <div class="pdf-wrapper">
                            <iframe class="pdf-iframe" data-src="https://docs.google.com/gview?url={{ urlencode($manualUrl) }}&embedded=true"
                                    style="width:100%; height:600px; border:0;"></iframe>

                            {{-- <div class="mt-2">
                                <a href="{{ $manualUrl }}" target="_blank" rel="noopener noreferrer">Buka manual di tab baru</a>
                            </div> --}}
                        </div>
                    @else
                        <div class="p-4 text-center muted">Manual belum tersedia.</div>
                    @endif
                </div>

                <div class="tab-pane fade" id="qc" role="tabpanel">
                    @if(!empty($qcCertificateUrl))
                        <div class="pdf-wrapper">
                            <iframe class="pdf-iframe" data-src="https://docs.google.com/gview?url={{ urlencode($qcCertificateUrl) }}&embedded=true"
                                    style="width:100%; height:600px; border:0;"></iframe>

                            {{-- <div class="mt-2">
                                <a href="{{ $qcCertificateUrl }}" target="_blank" rel="noopener noreferrer">Buka sertifikat QC di tab baru</a>
                            </div> --}}
                        </div>
                    @else
                        <div class="p-4 text-center muted">Sertifikat QC belum tersedia atau tidak dapat diakses.</div>
                    @endif
                </div>

                <div class="tab-pane fade" id="warranty" role="tabpanel">
                    @if($product->warranty_card)
                        @php
                            $warrantyUrl = 'https://stesy.beacontelemetry.com/product/warranty/' . $product->warranty_card;
                        @endphp
                        <div class="pdf-wrapper">
                            <iframe class="pdf-iframe" data-src="https://docs.google.com/gview?url={{ urlencode($warrantyUrl) }}&embedded=true"
                                    style="width:100%; height:600px; border:0;"></iframe>

                            {{-- <div class="mt-2">
                                <a href="{{ $warrantyUrl }}" target="_blank" rel="noopener noreferrer">Buka garansi di tab baru</a>
                            </div> --}}
                        </div>
                    @else
                        <div class="p-4 text-center muted">Dokumen garansi belum tersedia.</div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    {{-- sidebar (baru: lebih menarik, tanpa QR) --}}
    <div class="col-lg-4">
        <aside class="sidebar-section">
            {{-- product quick card --}}
            <div class="glass-card mb-3 p-3">
                <div class="d-flex align-items-start gap-3">
                    <div style="flex:1;">
                        <h5 style="margin:0 0 6px 0;">{{ $product->name }}</h5>
                        <div class="muted small">Dokumentasi & dokumen resmi</div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success">Official</span>
                        <div class="mt-2">
                            <small class="muted">Updated</small><br>
                            <strong>{{ $product->updated_at->format('d M Y') }}</strong>
                        </div>
                    </div>
                </div>

                <hr class="my-2">

                {{-- quick doc list --}}
                <div class="mb-2">
                    <div class="meta-row">
                        <div class="meta-key">User Manual</div>
                        <div>
                            @if($product->manual_file)
                                <span class="doc-badge doc-available">Available</span>
                            @else
                                <span class="doc-badge doc-soon">Coming soon</span>
                            @endif
                        </div>
                    </div>

                    <div class="meta-row">
                        <div class="meta-key">Sertifikat QC</div>
                        <div>
                            @if($product->qc_certificate)
                                <span class="doc-badge doc-available">Available</span>
                            @else
                                <span class="doc-badge doc-soon">Coming soon</span>
                            @endif
                        </div>
                    </div>


                    <div class="meta-row">
                        <div class="meta-key">Garansi / Kartu</div>
                        <div>
                            @if($product->warranty_card)
                                <span class="doc-badge doc-available">Available</span>
                            @else
                                <span class="doc-badge doc-soon">Coming soon</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- support card --}}
            <div class="glass-card mb-3 p-3">
                <h6 style="margin:0 0 8px 0;">Butuh Bantuan?</h6>
                <p class="support-small mb-2">Tim support siap membantu: teknis, klaim garansi, atau permintaan dokumen.</p>

                <div class="d-grid gap-2 mb-2">
                    <a href="https://wa.me/628112632151?text=Halo%20saya%20ingin%20bertanya" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        viewBox="0 0 16 16">
                            <path d="M13.601 2.326A7.928 7.928 0 008 .073C3.589.073.073 3.59.073 8c0 1.414.37 2.793 1.071
                                    4.001L0 16l4.064-1.058A7.93 7.93 0 008 15.927c4.411 0 7.927-3.516
                                    7.927-7.927a7.928 7.928 0 00-2.326-5.674zM8 14.563a6.55 6.55 0 01-3.356-.92l-.24-.142-2.408.627.644-2.345-.157-.24A6.551
                                    6.551 0 011.438 8c0-3.614 2.948-6.563 6.562-6.563 1.753 0 3.401.684
                                    4.64 1.923A6.532 6.532 0 0114.562 8c0 3.614-2.948 6.563-6.562
                                    6.563z"/>
                            <path d="M11.603 9.665c-.197-.099-1.168-.577-1.35-.644-.182-.066-.315-.099-.447.099s-.513.644-.629.775c-.116.132-.232.149-.43.05-.197-.1-.833-.307-1.587-.98-.586-.521-.981-1.163-1.096-1.361-.115-.198-.012-.305.087-.403.089-.088.197-.231.296-.347.099-.116.132-.198.198-.33.066-.132.033-.248-.017-.347-.05-.1-.447-1.079-.612-1.476-.162-.389-.326-.335-.447-.34l-.38-.007c-.132 0-.347.05-.53.248s-.694.678-.694 1.653.71 1.917.81 2.049c.099.132 1.393 2.13 3.379 2.986.472.204.84.326
                                    1.127.417.474.15.905.129 1.246.078.38-.056 1.168-.477 1.333-.938.164-.462.164-.857.115-.938-.05-.082-.18-.132-.376-.231z"/>
                        </svg> Hubungi via WhatsApp: 628112632151
                    </a>
                    <a href="mailto:info@bejogja.com" class="btn btn-outline-secondary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-envelope-fill" viewBox="0 0 16 16">
                            <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555zM0 4.697v7.104l5.803-3.558L0 4.697zM6.761 8.83l-6.57
                            4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.143l-6.57-4.027L8 9.586l-1.239-.757zM16
                            4.697l-5.803 3.546L16 11.801V4.697z"/>
                        </svg> Email: info@bejogja.com
                    </a>
                </div>

                <small class="muted">Jam kerja: Senin-Jumat, 09:00-17:00</small>
            </div>

            {{-- FAQ singkat --}}
            <div class="glass-card p-3">
                <h6 style="margin:0 0 8px 0;">FAQ Cepat</h6>
                <div class="accordion" id="faqAcc">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">Bagaimana cara mengklaim garansi?</button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
                          <div class="accordion-body small muted">
                            Siapkan bukti pembelian & serial number, lalu hubungi tim support.
                          </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">Apakah dokumen ini resmi?</button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
                          <div class="accordion-body small muted">
                            Ya — semua file yang tersedia di sini adalah dokumen resmi dari pabrikan dan diverifikasi oleh tim QC kami.
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Lazy load iframe content when a tab becomes active (Bootstrap)
    const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"], [data-toggle="tab"]');

    function loadIframeForPane(pane) {
        const iframe = pane.querySelector('iframe.pdf-iframe');
        if (!iframe) return;
        if (!iframe.getAttribute('src') && iframe.dataset.src) {
            iframe.setAttribute('src', iframe.dataset.src);
        }
    }

    // Load first visible (active) pane on page load
    document.querySelectorAll('.tab-pane.show.active').forEach(pane => loadIframeForPane(pane));

    // Listen for bootstrap tab shown events (v5 & fallback v4)
    tabLinks.forEach(link => {
        link.addEventListener('shown.bs.tab', function (e) {
            const targetSelector = e.target.getAttribute('data-bs-target') || e.target.getAttribute('href');
            if (!targetSelector) return;
            const pane = document.querySelector(targetSelector);
            if (pane) loadIframeForPane(pane);
        });

        // fallback for BS4
        link.addEventListener('shown.bs.tab', function (e) {
            const targetSelector = e.target.getAttribute('href');
            const pane = document.querySelector(targetSelector);
            if (pane) loadIframeForPane(pane);
        });
    });
});
</script>
@endpush
@endsection
