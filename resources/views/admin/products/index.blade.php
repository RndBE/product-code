@extends('layouts.appAdmin')

@push('styles')
<style>
    .admin-header {
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    .product-thumb {
        width:70px; height:50px;
        object-fit:cover;
        border-radius:6px;
        border:1px solid rgba(0,0,0,0.06);
    }
    .small-muted {
        color: var(--muted);
        font-size:.9rem;
    }
    .action-btns .btn { min-width:72px; }
    .table thead th { vertical-align: middle; }
    .search-input { max-width:520px; }

    .label {
        border: 1px solid #000;
        border-radius: 8px;
        margin: 10px;
        padding: 6px;
        display: flex;
        flex-direction: column;
        justify-content: space-between; /* membuat item paling bawah tetap di bawah */
        text-align: center;
        background: #fff;
        page-break-inside: avoid;
        width: 60mm;   /* Lebar label fisik */
        height: 48mm;  /* Tinggi label fisik */
        box-sizing: border-box;
    }

    .label-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 4px;
    }

    .label-header .logo {
        flex: 1;
        text-align: center;
    }

    .label-header .logo img {
        display: inline-block;
        max-width: 100px;
        max-height: 60px;
    }

    .label-header .qr img {
        width: 90px;
        height: 90px;
    }

    /* Container untuk nama + barcode di bawah */
    .label-footer {
        display: flex;
        flex-direction: column;
        align-items: flex-start; /* nama di pojok kiri */
        justify-content: flex-end;
    }

    /* Nama produk di kiri atas barcode */
    .product-name {
        font-size: 13px;
        font-weight: 600;
        margin: 0;
        padding: 0;
        line-height: 1;
    }

    /* Barcode di bawah nama, tanpa jarak berlebih */
    .barcode {
        width: 100%;
        height: 50px;
        margin-top: 2px;
        margin-bottom: 0;
    }

    .download-btn {
        margin-top: 10px;
        background: #007bff;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .download-btn:hover {
        background: #0056b3;
    }
</style>

@endpush

@section('content')
    <div class="admin-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div>
            <h3 class="mb-0">Daftar Produk</h3>
            <div class="small-muted">Kelola produk, manual, sertifikat, dan dokumen resmi.</div>
        </div>

        {{-- Action buttons --}}
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                ➕ Tambah Produk
            </a>

            {{-- Tombol print label ada di sini, bukan di bawah tabel --}}
            <button type="submit" form="bulkPrintForm" id="printSelectedBtn" class="btn btn-success" disabled>
                🖨️ Print Label Terpilih
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" id="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" id="alert-error">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Table --}}
    <div class="table-responsive">
        <form id="bulkPrintForm" action="{{ route('products.bulkPrint') }}" method="POST" target="_blank">
            @csrf
            <table id="productsTable" class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th style="width:60px;">No</th>
                        <th>Produk</th>
                        <th>Kode Produksi</th>
                        <th style="width:120px;">Dokumen</th>
                        <th style="width:140px;">QR Code</th>
                        <th style="width:140px;">Tanggal</th>
                        <th style="width:170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="align-middle text-center">
                            <input type="checkbox" name="selected_products[]" value="{{ $product->id }}" class="rowCheckbox">
                        </td>
                        <td class="align-middle">{{ $loop->iteration }}</td>
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
                            <div class="small-muted">{{ $product->kode_list }}</div>
                        </td>

                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                @if($product->manual_file)
                                    <a class="small-muted" href="{{ 'https://stesy.beacontelemetry.com/product/manual/' . $product->manual_file }}" target="_blank">📘 Manual</a>
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
                                    <a class="small-muted mt-1" href="{{ 'https://stesy.beacontelemetry.com/product/warranty/'. $product->warranty_card }}" target="_blank">📑 Garansi</a>
                                @else
                                    <span class="small-muted">—</span>
                                @endif
                            </div>
                        </td>

                        <td class="align-middle text-center">
                            {{-- Label Produk --}}
                            <div class="label-container">
                                <div class="label shadow-sm border rounded bg-white p-2 mx-auto label-downloadable"
                                    id="label-{{ $product->id }}">

                                    {{-- Header Logo & QR --}}
                                    <div class="d-flex justify-content-between align-items-center mb-1 label-header">
                                        <div class="logo">
                                            <img src="{{ asset('img/logo_be2.png') }}" alt="Logo" class="logo">
                                        </div>
                                        <div class="qr">
                                            @if($product->qr_base64)
                                                <img src="{{ $product->qr_base64 }}" alt="QR" class="qr-img">
                                            @else
                                                <img src="{{ asset('img/no_qr.png') }}" alt="No QR" class="qr-img">
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Footer (Nama + Barcode) di bawah label --}}
                                    <div class="label-footer">
                                        <div class="product-name">
                                            {{ $product->name ?? '-' }}
                                        </div>

                                        <svg class="barcode"
                                            jsbarcode-format="CODE128"
                                            jsbarcode-value="{{ $product->serial_number ?? '000000' }}"
                                            jsbarcode-textmargin="0"
                                            jsbarcode-fontsize="50"
                                            jsbarcode-width="5"
                                            jsbarcode-height="120">
                                        </svg>
                                    </div>
                                </div>

                                <button type="button"
                                    class="btn btn-sm btn-outline-primary mt-2 download-label-btn"
                                    data-target="label-{{ $product->id }}">
                                    ⬇️ Download PNG
                                </button>
                            </div>
                        </td>

                        <td class="align-middle">
                            <div class="small-muted">{{ $product->created_at->format('d M Y') }}</div>
                            <div class="small-muted">Updated: {{ $product->updated_at->format('d M Y') }}</div>
                        </td>

                        <td class="align-middle">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('products.edit', $product->id) }}"
                                class="btn btn-sm btn-outline-warning">
                                    ✏️ Edit
                                </a>

                                <a href="{{ route('user.manual', $product->slug) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary">
                                    📄 View
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-delete"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}" data-serial="{{ $product->serial_number }}">
                                    🗑️ Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center small-muted py-4">Tidak ada produk ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>
    @push('scripts')
    <!-- Script JS -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            JsBarcode(".barcode").init();

            const MM_TO_PX = 3.78;
            const LABEL_WIDTH_MM = 60;
            const LABEL_HEIGHT_MM = 48; // tinggi disamakan 48mm
            const LABEL_WIDTH_PX = LABEL_WIDTH_MM * MM_TO_PX;
            const LABEL_HEIGHT_PX = LABEL_HEIGHT_MM * MM_TO_PX;

            document.querySelectorAll('.download-label-btn').forEach(button => {
                button.addEventListener('click', async function () {
                    const targetId = this.dataset.target;
                    const labelElement = document.getElementById(targetId);
                    if (!labelElement) return;

                    JsBarcode(labelElement.querySelector(".barcode")).init();

                    // Simpan style lama
                    const oldStyle = labelElement.getAttribute('style');

                    // Pastikan ukuran sesuai mm sebelum render
                    labelElement.style.width = LABEL_WIDTH_PX + 'px';
                    labelElement.style.height = LABEL_HEIGHT_PX + 'px';
                    labelElement.style.background = '#fff';
                    labelElement.style.border = '1px solid #000';
                    labelElement.style.padding = '4px';

                    const canvas = await html2canvas(labelElement, {
                        scale: 6,
                        useCORS: true,
                        backgroundColor: '#ffffff',
                        width: LABEL_WIDTH_PX,
                        height: LABEL_HEIGHT_PX,
                    });

                    // Kembalikan style lama
                    labelElement.setAttribute('style', oldStyle || '');

                    const link = document.createElement('a');
                    link.download = `Label_{{ $product->serial_number ?? 'produk' }}_{{ Str::slug($product->name ?? 'Produk') }}.png`;
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.rowCheckbox');
            const printBtn = document.getElementById('printSelectedBtn');

            function toggleButton() {
                const checked = document.querySelectorAll('.rowCheckbox:checked').length;
                printBtn.disabled = checked === 0;
            }

            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                toggleButton();
            });

            checkboxes.forEach(cb => cb.addEventListener('change', toggleButton));
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Delete dengan form dinamis (tidak nested)
            document.querySelectorAll('.btn-delete').forEach(function(btn) {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const serial_number = this.dataset.serial  || 'produk ini';

                    Swal.fire({
                        title: `Hapus ${name}(${serial_number})?`,
                        text: "Tindakan ini tidak bisa dikembalikan.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // buat form dinamis dan submit
                            const form = document.createElement('form');
                            form.method = 'POST';
                            // action ke route destroy: admin/products/{id}
                            form.action = "{{ url('admin/products') }}/" + id;
                            // CSRF token
                            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                            const inputToken = document.createElement('input');
                            inputToken.type = 'hidden';
                            inputToken.name = '_token';
                            inputToken.value = token;
                            form.appendChild(inputToken);
                            // method spoofing DELETE
                            const method = document.createElement('input');
                            method.type = 'hidden';
                            method.name = '_method';
                            method.value = 'DELETE';
                            form.appendChild(method);

                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    {{-- DataTables JS & CSS --}}
    <script>
        $(document).ready(function () {
            $('#productsTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ produk per halaman",
                    "info": "Menampilkan _START_ - _END_ dari _TOTAL_ produk",
                    "paginate": {
                        "previous": "Sebelumnya",
                        "next": "Berikutnya"
                    }
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function autoHideAlert(alertId, timeout = 4000) {
                const alertBox = document.getElementById(alertId);
                if (alertBox) {
                    setTimeout(() => {
                        // Tambahkan animasi fade out
                        alertBox.classList.add('fade');
                        alertBox.classList.remove('show');

                        // Hapus elemen dari DOM setelah animasi selesai (300ms default Bootstrap)
                        setTimeout(() => {
                            alertBox.remove();
                        }, 300);
                    }, timeout);
                }
            }

            autoHideAlert('alert-success', 4000); // success 4 detik
            autoHideAlert('alert-error', 6000);   // error 6 detik
        });
    </script>
@endpush
@endsection


