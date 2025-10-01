@extends('layouts.appAdmin')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">{{ isset($product) ? 'Edit Produk' : 'Tambah Produk' }}</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($product))
                    @method('PUT')
                @endif

                {{-- Nama Produk dari Inventory --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Serial Number Produk</label>

                    @if(isset($product))
                        {{-- Saat edit: select disabled + hidden input --}}
                        <input type="hidden" name="qc_product_id" value="{{ $product->produk_jadi_list_id }}">
                        <select class="form-select" disabled>
                            <option value="">-- Pilih Produk dari Inventory --</option>
                            @foreach($inventoryProducts as $inv)
                                <option value="{{ $inv['id'] }}"
                                    {{ $product->produk_jadi_list_id == $inv['id'] ? 'selected' : '' }}>
                                    {{ $inv['produk_jadi']['nama_produk'] }}
                                    | SN: {{ $inv['serial_number'] }}
                                    | Kode: {{ $inv['kode_list'] }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <select name="qc_product_id" class="form-select @error('qc_product_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Produk dari Inventory --</option>
                            @foreach($inventoryProducts as $inv)
                                <option value="{{ $inv['id'] }}"
                                    {{ in_array($inv['id'], $usedIds) ? 'disabled' : '' }}
                                    {{ old('qc_product_id') == $inv['id'] ? 'selected' : '' }}>
                                    {{ $inv['produk_jadi']['nama_produk'] }}
                                    | SN: {{ $inv['serial_number'] }}
                                    | Kode: {{ $inv['kode_list'] }}
                                    @if(in_array($inv['id'], $usedIds)) (Sudah Ada) @endif
                                </option>
                            @endforeach
                        </select>
                    @endif

                    @error('qc_product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>




                {{-- Upload Manual --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Upload Manual (PDF/DOCX)</label>
                    <input type="file" name="manual_file" class="form-control @error('manual_file') is-invalid @enderror">
                    @error('manual_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if(isset($product) && $product->manual_file)
                        <small class="d-block mt-2">
                            File saat ini:
                            <a href="{{ 'https://beacontelemetry.com/ProductManual/' . $product->manual_file }}" target="_blank" class="text-primary fw-bold">
                                Lihat Manual
                            </a>
                        </small>
                    @endif
                </div>

                {{-- Upload Kartu Garansi --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Upload Kartu Garansi (PDF/DOCX)</label>
                    <input type="file" name="warranty_card" class="form-control @error('warranty_card') is-invalid @enderror">
                    @error('warranty_card')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if(isset($product) && $product->warranty_card)
                        <small class="d-block mt-2">
                            File saat ini:
                            <a href="{{ 'https://beacontelemetry.com/ProductManual/' . $product->warranty_card }}" target="_blank" class="text-primary fw-bold">
                                Lihat Kartu Garansi
                            </a>
                        </small>
                    @endif
                </div>

                {{-- Konten (CKEditor) --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Konten Produk</label>
                    <textarea name="content" id="editor" class="ckeditor form-control @error('content') is-invalid @enderror" rows="10">
                        {{ old('content', $product->content ?? '') }}
                    </textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
