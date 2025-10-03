<?php

namespace App\Http\Controllers\User;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class UserManualController extends Controller
{
    public function show($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $qcCertificate = null;
        $qcCertificateUrl = null;

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => config('services.inventory.key'),
                'Accept'    => 'application/json',
            ])->timeout(5)
            ->get(config('services.inventory.url') . '/qc-produk-jadi/' . $product['produk_jadi_list_id']);

            if ($response->successful()) {
                $inv = $response->json('data');
                if (!empty($inv['qc2']['laporan_qc'])) {
                    $qcCertificate = $inv['qc2']['laporan_qc'];
                } elseif (!empty($inv['qc1']['laporan_qc'])) {
                    $qcCertificate = $inv['qc1']['laporan_qc'];
                }
            }
        } catch (\Throwable $e) {
            // biarkan qcCertificate null
            $qcCertificate = null;
        }

        if ($qcCertificate) {
            // Pastikan url absolut dan https jika perlu
            $qcCertificateUrl = Str::startsWith($qcCertificate, ['http://', 'https://'])
                ? $qcCertificate
                : rtrim(config('services.inventory.url_storage'), '/') . '/' . ltrim($qcCertificate, '/');

            // optional: cek secara cepat apakah file dapat diakses
            try {
                $head = Http::timeout(3)->withoutVerifying()->head($qcCertificateUrl);
                if (! $head->successful()) {
                    // tidak reachable → anggap null
                    $qcCertificateUrl = null;
                }
            } catch (\Throwable $e) {
                $qcCertificateUrl = null;
            }
        }

        $product->qc_certificate = $qcCertificate; // original value (jika mau)
        // pass juga url final atau null
        return view('user.manual', [
            'product' => $product,
            'qcCertificateUrl' => $qcCertificateUrl,
        ]);
    }

}

