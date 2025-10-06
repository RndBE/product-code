<?php

namespace App\Http\Controllers\Admin;

use Log;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use App\Http\Controllers\Controller;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemException;
use Illuminate\Http\Client\ConnectionException;

class ProductController extends Controller
{
    public function index() {
        $products = Product::latest()->paginate(10);

        foreach ($products as $product) {
            try {
                $response = Http::withHeaders([
                    'X-API-KEY' => config('services.inventory.key'),
                    'Accept'    => 'application/json',
                ])->get(config('services.inventory.url') . '/qc-produk-jadi/' . $product->produk_jadi_list_id);

                if ($response->successful()) {
                    $inv = $response->json('data');

                    // Sertifikat QC
                    $qcCertificate = null;
                    if (!empty($inv['qc2']['laporan_qc'])) {
                        $qcCertificate = $inv['qc2']['laporan_qc'];
                    } elseif (!empty($inv['qc1']['laporan_qc'])) {
                        $qcCertificate = $inv['qc1']['laporan_qc'];
                    }

                    // Gambar produk jadi
                    $gambar = $inv['produksi_produk_jadi']['data_produk_jadi']['gambar'] ?? null;

                    // inject ke tiap product
                    $product->qc_certificate = $qcCertificate;
                    $product->gambar_produk_jadi = $gambar;
                }
            } catch (\Throwable $e) {
                $product->qc_certificate = null;
            }
        }

        return view('admin.products.index', compact('products'));
    }
    public function bulkPrint(Request $request)
    {
        // dd('masuk controller', $request->all());
        $ids = $request->input('selected_products', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada produk dipilih.');
        }

        $products = Product::whereIn('id', $ids)->get();
        // dd($products);
        // contoh sederhana: tampilkan view label
        return view('admin.products.print-labels', compact('products'));
    }

    public function create()
    {
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => config('services.inventory.key'),
                'Accept'    => 'application/json',
            ])->get(config('services.inventory.url') . '/qc-produk-jadi');

            $qcProducts = $response->successful() ? ($response->json('data') ?? []) : [];
        } catch (ConnectionException $e) {
            $qcProducts = [];
        } catch (\Exception $e) {
            $qcProducts = [];
        }

        // Ambil semua ID produk inventory yang sudah tersimpan di DB
        $usedIds = Product::pluck('produk_jadi_list_id')->toArray();

        return view('admin.products.form', [
            'inventoryProducts' => $qcProducts,
            'usedIds' => $usedIds,
        ]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'qc_product_id'   => 'required|integer',
            'name'            => 'nullable|string|max:255',
            'manual_file'     => 'nullable|file|mimes:pdf,docx|max:5120',
            'warranty_card'   => 'nullable|file|mimes:pdf,docx|max:5120',
            'content'         => 'nullable|string',
        ], [
            'manual_file.max'   => 'Manual file tidak boleh lebih dari 5 MB.',
            'manual_file.mimes' => 'Manual file harus berupa file PDF atau DOCX.',
            'warranty_card.max'   => 'Warranty card tidak boleh lebih dari 5 MB.',
            'warranty_card.mimes' => 'Warranty card harus berupa file PDF atau DOCX.',
        ]);

        // Ambil detail produk dari Inventory
        $response = Http::withHeaders([
            'X-API-KEY' => config('services.inventory.key'),
            'Accept'    => 'application/json',
        ])->get(config('services.inventory.url') . '/qc-produk-jadi/' . $data['qc_product_id']);

        if (!$response->successful()) {
            return back()->withErrors(['qc_product_id' => 'Gagal ambil data dari Inventory!']);
        }

        $inv   = $response->json('data');
        // dd($inv);
        $slug  = Str::slug($inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk']) . '-'. ($inv['serial_number']);
        $today = date('Ymd_His');
        // dd($slug);
        $manualFileName = null;
        $warrantyFileName = null;

        // Upload manual_file ke server penerima
        if ($request->hasFile('manual_file')) {
            $manualFile = $request->file('manual_file');

            $uploadResponse = Http::withToken(config('services.upload.token'))
                ->attach(
                    'manual', // key sesuai API server penerima
                    fopen($manualFile->getRealPath(), 'r'),
                    'manual_' . $today . '_' . $slug . '.' . $manualFile->getClientOriginalExtension()
                )->withoutVerifying()
                ->post(config('services.upload.url') . '/api/do_upload'); // tambahkan path

            if ($uploadResponse->successful()) {
                $json = $uploadResponse->json();
                $manualFileName = $json['manual']['file_name'] ?? null; // simpan file_name
            }
        }


        // Upload warranty_card ke server penerima
        if ($request->hasFile('warranty_card')) {
            $warrantyFile = $request->file('warranty_card');

            $uploadResponse = Http::withToken(config('services.upload.token'))
                ->attach(
                    'warranty', // key sesuai API server penerima
                    fopen($warrantyFile->getRealPath(), 'r'),
                    'warranty_' . $today . '_' . $slug . '.' . $warrantyFile->getClientOriginalExtension()
                )->withoutVerifying()
                ->post(config('services.upload.url') . '/api/do_upload');

            if ($uploadResponse->successful()) {
                $json = $uploadResponse->json();
                $warrantyFileName = $json['warranty']['file_name'] ?? null; // simpan file_name
            }
        }

        // Simpan data produk
        $product = Product::create([
            'name'               => $inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk'],
            'slug'               => $slug,
            'manual_file'        => $manualFileName,
            'warranty_card'      => $warrantyFileName,
            'content'            => $request->content,
            'produk_jadi_list_id'=> $inv['id'],
            'produk_jadi_id'     => $inv['produk_jadi_id'],
            'serial_number'      => $inv['serial_number'],
            'nama_produk'        => $inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk'],
            'kode_list'          => $inv['kode_list'],
        ]);

        // Generate QR code
        $qrName = 'qrcode_' . $today . '_' . $slug . '.png';
        $result = (new Builder(
            writer: new PngWriter(),
            data: route('user.manual', $product->slug),
            size: 300,
            margin: 10
        ))->build();

        $uploadResponse = Http::withToken(config('services.upload.token'))
        ->attach(
            'qr_code', // key sesuai API server penerima
            $result->getString(),
            $qrName
        )->withoutVerifying()
        ->post(config('services.upload.url') . '/api/do_upload');

        if ($uploadResponse->successful()) {
            $json = $uploadResponse->json();
            $qrPath = $json['qr_code']['file_name'] ?? null;

            if ($qrPath) {
                $product->update(['qr_code' => $qrPath]);
            }
        } else {
            return back()->withErrors(['qr_code' => 'Upload QR Code gagal ke server penerima']);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $product = Product::findOrFail($id);

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => config('services.inventory.key'),
                'Accept'    => 'application/json',
            ])->get(config('services.inventory.url') . '/qc-produk-jadi');

            $qcProducts = $response->successful() ? $response->json('data') : [];
        } catch (ConnectionException $e) {
            $qcProducts = [];
        } catch (\Exception $e) {
            $qcProducts = [];
        }

        return view('admin.products.form', [
            'product' => $product,
            'inventoryProducts' => $qcProducts,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'qc_product_id'   => 'required|integer',
            'name'            => 'nullable|string|max:255',
            'manual_file'     => 'nullable|file|mimes:pdf,docx',
            'warranty_card'   => 'nullable|file|mimes:pdf,docx',
            'content'         => 'nullable|string',
        ]);

        // Ambil detail produk dari Inventory
        $response = Http::withHeaders([
            'X-API-KEY' => config('services.inventory.key'),
            'Accept'    => 'application/json',
        ])->get(config('services.inventory.url') . '/qc-produk-jadi/' . $data['qc_product_id']);

        if (!$response->successful()) {
            return back()->withErrors(['qc_product_id' => 'Gagal ambil data dari Inventory!']);
        }

        $inv   = $response->json('data');
        $slug  = $product->slug;
        $today = date('Ymd_His');

        $manualFileName = $product->manual_file;
        $warrantyFileName = $product->warranty_card;

        // === Upload manual_file ke server penerima ===
        if ($request->hasFile('manual_file')) {
            $manualFile = $request->file('manual_file');
            $manualFileNameNew = 'manual_' . $today . '_' . $slug . '.' . $manualFile->getClientOriginalExtension();

            // Hapus file lama di server penerima
            if ($manualFileName) {
                Http::withToken(config('services.upload.token'))
                    ->asForm()->withoutVerifying()
                    ->post(config('services.upload.url') . '/api/delete_file', [
                        'file_name' => $manualFileName,
                        'folder'    => 'manual',
                    ]);
            }

            // Upload file baru
            $uploadResponse = Http::withToken(config('services.upload.token'))
                ->attach('manual', fopen($manualFile->getRealPath(), 'r'), $manualFileNameNew)
                ->withoutVerifying()
                ->post(config('services.upload.url') . '/api/do_upload');

            if ($uploadResponse->successful()) {
                $json = $uploadResponse->json();
                $manualFileName = $json['manual']['file_name'] ?? $manualFileNameNew;
            } else {
                return back()->withErrors(['manual_file' => 'Upload manual gagal!']);
            }
        }

        // === Upload warranty_card ke server penerima ===
        if ($request->hasFile('warranty_card')) {
            $warrantyFile = $request->file('warranty_card');
            $warrantyFileNameNew = 'warranty_' . $today . '_' . $slug . '.' . $warrantyFile->getClientOriginalExtension();

            // Hapus file lama di server penerima
            if ($warrantyFileName) {
                Http::withToken(config('services.upload.token'))
                    ->asForm()->withoutVerifying()
                    ->post(config('services.upload.url') . '/api/delete_file', [
                        'file_name' => $warrantyFileName,
                        'folder'    => 'warranty',
                    ]);
            }

            // Upload file baru
            $uploadResponse = Http::withToken(config('services.upload.token'))
                ->attach('warranty', fopen($warrantyFile->getRealPath(), 'r'), $warrantyFileNameNew)
                ->withoutVerifying()
                ->post(config('services.upload.url') . '/api/do_upload');

            if ($uploadResponse->successful()) {
                $json = $uploadResponse->json();
                $warrantyFileName = $json['warranty']['file_name'] ?? $warrantyFileNameNew;
            } else {
                return back()->withErrors(['warranty_card' => 'Upload warranty gagal!']);
            }
        }

        // Update data product di database
        $product->update([
            'name'               => $inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk'],
            'slug'               => $slug,
            'manual_file'        => $manualFileName,
            'warranty_card'      => $warrantyFileName,
            'content'            => $request->content,
            'produk_jadi_list_id'=> $inv['id'],
            'produk_jadi_id'     => $inv['produk_jadi_id'],
            'serial_number'      => $inv['serial_number'],
            'nama_produk'        => $inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk'],
            'kode_list'          => $inv['kode_list'],
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui!');
    }


    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        // Hapus file di server penerima
        $filesToDelete = [
            'manual'   => $product->manual_file,
            'warranty' => $product->warranty_card,
            'qr_code'  => $product->qr_code,
        ];

        // dd($filesToDelete);
        foreach ($filesToDelete as $folder => $fileName) {
            if ($fileName) {
                try {
                    $response = Http::withToken(config('services.upload.token'))
                        ->asForm()->withoutVerifying()
                        ->post(config('services.upload.url') . '/api/delete_file', [
                            'file_name' => $fileName,
                            'folder'    => $folder,
                        ]);

                    if (!$response->successful()) {
                        // Gagal hapus file di server penerima
                        return back()->withErrors([
                            'delete_file' => "Gagal menghapus file {$fileName} di server penerima: " . $response->body()
                        ]);
                    }
                } catch (\Exception $e) {
                    return back()->withErrors([
                        'delete_file' => "Error hapus file {$fileName}: " . $e->getMessage()
                    ]);
                }
            }
        }

        // Hapus record di database
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus!');
    }



}
