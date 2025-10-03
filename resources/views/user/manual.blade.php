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
                    Seluruh dokumen di atas dapat diakses secara digital langsung melalui platform ini.
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

                <div class="mt-2">
                    <a href="{{ $manualUrl }}" target="_blank" rel="noopener noreferrer">Buka manual di tab baru</a>
                </div>
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

                <div class="mt-2">
                    <a href="{{ $qcCertificateUrl }}" target="_blank" rel="noopener noreferrer">Buka sertifikat QC di tab baru</a>
                </div>
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

                <div class="mt-2">
                    <a href="{{ $warrantyUrl }}" target="_blank" rel="noopener noreferrer">Buka garansi di tab baru</a>
                </div>
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
                    <a href="tel:+62123456789" class="btn btn-outline-secondary btn-sm">📞 Hubungi: +62 12 3456 789</a>
                    <a href="mailto:support@example.com" class="btn btn-outline-secondary btn-sm">✉️ Email: support@example.com</a>
                </div>

                <small class="muted">Jam kerja: Senin–Jumat, 09:00–17:00</small>
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
                            Siapkan bukti pembelian & serial number, lalu hubungi tim support atau kunjungi halaman klaim garansi di website resmi.
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
