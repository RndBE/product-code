@extends('layouts.appAdmin')

@push('styles')
<style>
    /* sedikit styling untuk admin table */
    .admin-header { margin-top: 1rem; margin-bottom: 1rem; }
    .product-thumb { width:70px; height:50px; object-fit:cover; border-radius:6px; border:1px solid rgba(0,0,0,0.06); }
    .small-muted { color: var(--muted); font-size:.9rem; }
    .action-btns .btn { min-width:72px; }
    .table thead th { vertical-align: middle; }
    .search-input { max-width:520px; }
</style>
@endpush

@section('content')
<div class="admin-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
    {{-- <div>
        <h3 class="mb-0">Daftar Produk</h3>
        <div class="small-muted">Kelola produk, manual, sertifikat, dan dokumen resmi.</div>
    </div> --}}

    <div class="d-flex gap-2 align-items-center">
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Tambah Produk</a>
    </div>
</div>

{{-- Search & PerPage --}}
<div class="row mb-3 align-items-center">
    <div class="col-md-6 mb-2 mb-md-0">
        <form id="searchForm" method="GET" action="{{ route('products.index') }}" class="d-flex gap-2">
            <div class="input-group search-input">
                <span class="input-group-text"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M11.742 10.344a6 6 0 1 0-1.397 1.397l3.85 3.85 1.397-1.397-3.85-3.85zM12 6a6 6 0 1 1-12 0 6 6 0 0 1 12 0z"/></svg></span>
                <input id="q" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Cari nama produk, slug, atau serial..." />
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>
    </div>

    <div class="col-md-6 text-md-end">
        <form id="perpageForm" method="GET" action="{{ route('products.index') }}" class="d-inline-block">
            <input type="hidden" name="q" value="{{ $q ?? '' }}">
            <label class="small-muted me-2">Tampilkan</label>
            <select name="perPage" onchange="document.getElementById('perpageForm').submit()" class="form-select d-inline-block w-auto">
                @foreach([5,10,20,50,100] as $n)
                    <option value="{{ $n }}" @selected((int)($perPage ?? 10) === $n)>{{ $n }} / hal</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

{{-- Alerts --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Table --}}
<div class="card mb-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    {{-- <tr>
                        <th style="width:60px;">No</th>
                        <th>Produk</th>
                        <th>Kode Produksi</th>
                        <th style="width:120px;">Dokumen</th>
                        <th style="width:140px;">QR Code</th>
                        <th style="width:140px;">Tanggal</th>
                        <th style="width:170px;">Aksi</th>
                    </tr> --}}
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="align-middle">{{ $loop->iteration + ($products->currentPage()-1) * $products->perPage() }}</td>

                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-3">
                                @if($product->gambar_produk_jadi)
                                    <img src="{{ Str::startsWith($product->gambar_produk_jadi, ['http://', 'https://'])
                                        ? $product->gambar_produk_jadi
                                        : config('services.inventory.url_storage') . '/' . ltrim($product->gambar_produk_jadi, '/') }}"
                                        alt="Gambar Produk Jadi"
                                        class="img-thumbnail mt-2"
                                        style="max-width: 120px;">
                                @endif

                                <div>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    <div class="small-muted">{{ $product->serial_number }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <div class="small-muted">{{ $product->kode_list }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                @if($product->manual_file)
                                    <a class="small-muted" href="{{ 'https://beacontelemetry.com/ProductManual/' . $product->manual_file }}" target="_blank">📘 Manual</a>
                                @else
                                    <span class="small-muted">—</span>
                                @endif

                                @if($product->qc_certificate)
                                    <a class="small-muted mt-1"
                                    href="{{ Str::startsWith($product->qc_certificate, ['http://', 'https://'])
                                            ? $product->qc_certificate
                                            : rtrim(config('services.inventory.url_storage'), '/') . '/' . ltrim($product->qc_certificate, '/') }}"
                                    target="_blank">
                                        🏅 Sertifikat QC
                                    </a>
                                @else
                                    <span class="small-muted">—</span>
                                @endif


                                @if($product->warranty_card)
                                    <a class="small-muted mt-1" href="{{ 'https://beacontelemetry.com/ProductManual/' . $product->warranty_card }}" target="_blank">📑 Garansi</a>
                                @else
                                    <span class="small-muted">—</span>
                                @endif
                            </div>
                        </td>

                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                @if($product->qr_code)
                                    <img
                                        src="{{ 'https://beacontelemetry.com/ProductManual/' . $product->qr_code }}"
                                        alt="QR Code"
                                        style="width:100px; height:auto;"
                                    >
                                @else
                                    <span class="small-muted">—</span>
                                @endif
                            </div>
                        </td>


                        <td class="align-middle">
                            <div class="small-muted">{{ $product->created_at->format('d M Y') }}</div>
                            <div class="small-muted">Updated: {{ $product->updated_at->format('d M Y') }}</div>
                        </td>

                        <td class="align-middle action-btns">
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>

                            <a href="{{ route('user.manual', $product->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>

                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center small-muted py-4">Tidak ada produk ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- pagination + summary --}}
<div class="d-flex justify-content-between align-items-center">
    <div class="small-muted">
        Menampilkan <strong>{{ $products->firstItem() ?? 0 }}</strong> – <strong>{{ $products->lastItem() ?? 0 }}</strong> dari <strong>{{ $products->total() }}</strong> produk
    </div>
    <div>
        {{ $products->links() }}
    </div>
</div>

@push('scripts')
<script>
    // konfirmasi hapus
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.delete-form').forEach(function(form) {
            form.addEventListener('submit', function(e){
                if (!confirm('Yakin ingin menghapus produk ini? Tindakan ini tidak bisa dikembalikan.')) {
                    e.preventDefault();
                }
            });
        });

        // debounce untuk search (agar tidak mengirim tiap ketikan)
        let timer = null;
        const qInput = document.getElementById('q');
        if (qInput) {
            qInput.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    // submit form otomatis setelah 600ms
                    document.getElementById('searchForm').submit();
                }, 600);
            });
        }
    });
</script>
@endpush
@endsection
