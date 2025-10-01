<?php

namespace App\Http\Controllers\User;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class UserManualController extends Controller
{
    public function show($slug) {
        $product = Product::where('slug', $slug)->firstOrFail();
        // Ambil detail produk dari Inventory
        $response = Http::withHeaders([
            'X-API-KEY' => config('services.inventory.key'),
            'Accept'    => 'application/json',
        ])->get(config('services.inventory.url') . '/qc-produk-jadi/' . $product['produk_jadi_list_id']);

        if (!$response->successful()) {
            return back()->withErrors(['qc_product_id' => 'Gagal ambil data dari Inventory!']);
        }

        $inv = $response->json('data');
        // Tentukan sertifikat QC
        $qcCertificate = null;
        if (!empty($inv['qc2']) && !empty($inv['qc2']['laporan_qc'])) {
            $qcCertificate = $inv['qc2']['laporan_qc'];
        } elseif (!empty($inv['qc1']) && !empty($inv['qc1']['laporan_qc'])) {
            $qcCertificate = $inv['qc1']['laporan_qc'];
        }

        // Inject ke product biar bisa dipakai di Blade
        $product->qc_certificate = $qcCertificate;
        // dd($product->qc_certificate);

        // dd(config('services.inventory.url_storage') . '/' . $product->qc_certificate);


        return view('user.manual', compact('product'));
    }
}
