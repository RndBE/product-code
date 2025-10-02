<?php

namespace App\Http\Controllers\Admin;

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

        $inv = $response->json('data');
        $slug = Str::slug($inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk']) . '-' . time();
        $disk = Storage::disk('exabyte');
        $today = date('Ymd');

        // Upload manual file
        if ($request->hasFile('manual_file')) {
            $manualFile = $request->file('manual_file');
            $manualName = 'manual-'. $today . '_' . $slug . '.' . $manualFile->getClientOriginalExtension();
            $remotePath = $manualName; // langsung di root FTP

            try {
                $stream = fopen($manualFile->getRealPath(), 'r');
                if ($stream === false) {
                    return back()->withErrors(['manual_file' => 'Gagal membuka file manual untuk upload.']);
                }

                $ok = $disk->writeStream($remotePath, $stream);
                if (is_resource($stream)) fclose($stream);

                if ($ok === false) {
                    return back()->withErrors(['manual_file' => 'FTP writeStream gagal. Cek permission dan root folder di config.']);
                }

                $data['manual_file'] = $remotePath;
            } catch (FilesystemException | \Exception $e) {
                return back()->withErrors([
                    'manual_file' => 'Gagal meng-upload manual: ' . $e->getMessage()
                ]);
            }
        }


        // Upload warranty card
        if ($request->hasFile('warranty_card')) {
            $warrantyFile = $request->file('warranty_card');
            $warrantyName = 'warranty-'.$today . '_' . $slug . '.' . $warrantyFile->getClientOriginalExtension();
            $remotePath = $warrantyName;

            $stream = fopen($warrantyFile->getRealPath(), 'r');
            if ($stream === false) {
                return back()->withErrors(['warranty_card' => 'Gagal membuka file garansi untuk upload.']);
            }

            try {
                $ok = $disk->writeStream($remotePath, $stream);
                if (is_resource($stream)) fclose($stream);

                if ($ok === false) {
                    return back()->withErrors(['warranty_card' => 'FTP writeStream gagal. Cek permission dan root folder di config.']);
                }

                $data['warranty_card'] = $remotePath;
            } catch (FilesystemException | \Exception $e) {
                return back()->withErrors([
                    'warranty_card' => 'Gagal meng-upload garansi: ' . $e->getMessage()
                ]);
            }
        }

        // Simpan data produk
        $product = Product::create([
            'name'               => $inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk'],
            'slug'               => $slug,
            'manual_file'        => $data['manual_file'] ?? null,
            'warranty_card'      => $data['warranty_card'] ?? null,
            'content'            => $request->content,
            'produk_jadi_list_id'=> $inv['id'],
            'produk_jadi_id'     => $inv['produk_jadi_id'],
            'serial_number'      => $inv['serial_number'],
            'nama_produk'        => $inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk'],
            'kode_list'          => $inv['kode_list'],
        ]);

        // Generate QR code
        $today = now()->format('Ymd');
        $slug  = Str::slug($product->name, '-');
        $qrName = 'qrcode-' . $today . '_' . $slug . '.png';

        // simpan di folder public/qrcodes (akses via /storage/qrcodes/xxx.png)
        $qrPath = 'qrcodes/' . $qrName;

        // Build QR Code
        $result = (new Builder(
            writer: new PngWriter(),
            data: route('user.manual', $product->slug),
            size: 300,
            margin: 10
        ))->build();

        try {
            // Simpan ke local storage (disk public)
            $ok = Storage::disk('public')->put($qrPath, $result->getString());

            if ($ok === false) {
                return back()->withErrors(['qr_code' => 'Gagal menyimpan QR Code ke storage.']);
            }

            // update database dengan path relatif
            $product->update(['qr_code' => $qrPath]);

        } catch (FilesystemException | \Exception $e) {
            return back()->withErrors([
                'qr_code' => 'Gagal meng-upload QR Code: ' . $e->getMessage()
            ]);
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

        // === Ambil detail produk dari Inventory ===
        $response = Http::withHeaders([
            'X-API-KEY' => config('services.inventory.key'),
            'Accept'    => 'application/json',
        ])->get(config('services.inventory.url') . '/qc-produk-jadi/' . $data['qc_product_id']);

        if (!$response->successful()) {
            return back()->withErrors(['qc_product_id' => 'Gagal ambil data dari Inventory!']);
        }

        $inv   = $response->json('data');
        $slug  = Str::slug($inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk']) . '-' . time();
        $disk  = Storage::disk('exabyte');
        $today = date('Ymd');

        // === Upload manual file baru (ke FTP) ===
        if ($request->hasFile('manual_file')) {
            // hapus file lama kalau ada
            if ($product->manual_file && $disk->exists($product->manual_file)) {
                $disk->delete($product->manual_file);
            }

            $manualFile = $request->file('manual_file');
            $manualName = 'manual-'.$today.'_'.$slug.'.'.$manualFile->getClientOriginalExtension();

            $stream = fopen($manualFile->getRealPath(), 'r');
            if ($stream === false) {
                return back()->withErrors(['manual_file' => 'Gagal membuka file manual untuk upload.']);
            }

            try {
                $ok = $disk->writeStream($manualName, $stream);
                if (is_resource($stream)) fclose($stream);

                if ($ok === false) {
                    return back()->withErrors(['manual_file' => 'FTP gagal upload manual.']);
                }

                $data['manual_file'] = $manualName;
            } catch (FilesystemException | \Exception $e) {
                return back()->withErrors([
                    'manual_file' => 'Gagal upload manual: '.$e->getMessage()
                ]);
            }
        }


        // === Upload warranty card baru (ke FTP) ===
        if ($request->hasFile('warranty_card')) {
            // hapus file lama kalau ada
            if ($product->warranty_card && $disk->exists($product->warranty_card)) {
                $disk->delete($product->warranty_card);
            }

            $warrantyFile = $request->file('warranty_card');
            $warrantyName = 'warranty-'.$today.'_'.$slug.'.'.$warrantyFile->getClientOriginalExtension();

            $stream = fopen($warrantyFile->getRealPath(), 'r');
            if ($stream === false) {
                return back()->withErrors(['warranty_card' => 'Gagal membuka file garansi untuk upload.']);
            }

            try {
                $ok = $disk->writeStream($warrantyName, $stream);
                if (is_resource($stream)) fclose($stream);

                if ($ok === false) {
                    return back()->withErrors(['warranty_card' => 'FTP gagal upload garansi.']);
                }

                $data['warranty_card'] = $warrantyName;
            } catch (FilesystemException | \Exception $e) {
                return back()->withErrors([
                    'warranty_card' => 'Gagal upload garansi: '.$e->getMessage()
                ]);
            }
        }


        // === Update product dari Inventory + input user ===
        $product->update([
            'name'               => $inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk'],
            'slug'               => $slug,
            'manual_file'        => $data['manual_file'] ?? $product->manual_file,
            'warranty_card'      => $data['warranty_card'] ?? $product->warranty_card,
            'content'            => $request->content,
            'produk_jadi_list_id'=> $inv['id'],
            'produk_jadi_id'     => $inv['produk_jadi_id'],
            'serial_number'      => $inv['serial_number'],
            'nama_produk'        => $inv['produksi_produk_jadi']['data_produk_jadi']['nama_produk'],
            'kode_list'          => $inv['kode_list'],
        ]);

        if ($product->wasChanged('slug')) {
            // hapus QR lama kalau ada
            if ($product->qr_code && $disk->exists($product->qr_code)) {
                $disk->delete($product->qr_code);
            }

            $qrName = 'qrcode-'.$today.'_'.$slug.'.png';
            $qrPath = $qrName;

            $result = (new Builder(
                writer: new PngWriter(),
                data: route('user.manual', $product->slug),
                size: 300,
                margin: 10
            ))->build();

            try {
                $ok = $disk->put($qrPath, $result->getString());
                if ($ok === false) {
                    return back()->withErrors(['qr_code' => 'FTP gagal menyimpan QR Code.']);
                }
                $product->update(['qr_code' => $qrPath]);
            } catch (FilesystemException | \Exception $e) {
                return back()->withErrors(['qr_code' => 'Gagal upload QR Code: '.$e->getMessage()]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui!');
    }



    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        $disk = Storage::disk('exabyte'); // pakai disk yang sama dengan upload

        // hapus file manual
        if ($product->manual_file && $disk->exists($product->manual_file)) {
            $disk->delete($product->manual_file);
        }

        // hapus file warranty
        if ($product->warranty_card && $disk->exists($product->warranty_card)) {
            $disk->delete($product->warranty_card);
        }

        // hapus QR
        if ($product->qr_code && $disk->exists($product->qr_code)) {
            $disk->delete($product->qr_code);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus!');
    }

}
