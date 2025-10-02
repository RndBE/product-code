<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'manual_file'   => 'nullable|file|mimes:pdf,docx',
            'warranty_card' => 'nullable|file|mimes:pdf,docx',
            'qr_code'       => 'nullable|file|mimes:png,jpg,jpeg',
        ]);

        $paths = [];

        // Upload manual_file
        if ($request->hasFile('manual_file')) {
            $filename = $request->file('manual_file')->getClientOriginalName(); // pakai nama dari pengirim
            $path = $request->file('manual_file')->storeAs('uploads/manuals', $filename, 'public');
            $paths['manual_file'] = asset('storage/' . $path);
        }

        // Upload warranty_card
        if ($request->hasFile('warranty_card')) {
            $filename = $request->file('warranty_card')->getClientOriginalName();
            $path = $request->file('warranty_card')->storeAs('uploads/warranties', $filename, 'public');
            $paths['warranty_card'] = asset('storage/' . $path);
        }

        // Upload qr_code
        if ($request->hasFile('qr_code')) {
            $filename = $request->file('qr_code')->getClientOriginalName();
            $path = $request->file('qr_code')->storeAs('uploads/qrcodes', $filename, 'public');
            $paths['qr_code'] = asset('storage/' . $path);
        }

        return response()->json([
            'success' => true,
            'files'   => $paths
        ]);
    }
}


