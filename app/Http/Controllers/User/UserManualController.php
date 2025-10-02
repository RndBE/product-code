<?php

namespace App\Http\Controllers\User;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class UserManualController extends Controller
{
    public function show($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $qcCertificate = null;

        try {
            // Ambil detail produk dari Inventory
            $response = Http::withHeaders([
                'X-API-KEY' => config('services.inventory.key'),
                'Accept'    => 'application/json',
            ])->timeout(5) // kasih timeout biar tidak ngegantung
              ->get(config('services.inventory.url') . '/qc-produk-jadi/' . $product['produk_jadi_list_id']);

            if ($response->successful()) {
                $inv = $response->json('data');

                // Tentukan sertifikat QC
                if (!empty($inv['qc2']['laporan_qc'])) {
                    $qcCertificate = $inv['qc2']['laporan_qc'];
                } elseif (!empty($inv['qc1']['laporan_qc'])) {
                    $qcCertificate = $inv['qc1']['laporan_qc'];
                }
            }
        } catch (\Exception $e) {
            // Kalau server mati / unreachable → biarkan qcCertificate tetap null
            $qcCertificate = null;
        }

        // Inject ke product biar bisa dipakai di Blade
        $product->qc_certificate = $qcCertificate;

        return view('user.manual', compact('product'));
    }
}

