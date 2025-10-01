<?php

namespace App\Http\Controllers\User;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductVerificationController extends Controller
{
    public function index()
    {
        return view('home', [
            'result' => session('result'),
            'serial_number' => session('serial_number')
        ]);
    }


    public function generate()
    {
        $code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
        session(['captcha_code' => $code]);

        $image = imagecreate(120, 40);
        $bg = imagecolorallocate($image, 255, 255, 255);
        $textcolor = imagecolorallocate($image, 0, 0, 0);
        imagestring($image, 5, 10, 10, $code, $textcolor);

        ob_start();
        imagepng($image);
        $image_data = ob_get_clean();
        imagedestroy($image);

        return response($image_data)->header('Content-Type', 'image/png');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255',
            'captcha_code' => 'required|string'
        ]);

        if ($request->captcha_code !== session('captcha_code')) {
            return back()->withErrors(['captcha_code' => 'Kode verifikasi salah.'])->withInput();
        }

        $serial = $request->input('serial_number');
        $product = Product::where('serial_number', $serial)->first();

        if ($product) {
            $result = [
                'status' => 'valid',
                'serial_number' => $serial,
                'product' => $product,
            ];
        } else {
            $result = [
                'status' => 'invalid',
                'serial_number' => $serial,
            ];
        }

        // redirect ke halaman home GET, bawa flash data
        return redirect()->route('verify.index')
            ->with('result', $result)
            ->with('serial_number', $serial);
    }



}
