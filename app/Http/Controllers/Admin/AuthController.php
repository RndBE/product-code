<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Debugging: cek nilai is_admin
            // dd($user);

            if (! $user->is_admin) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun tidak memiliki akses admin.']);
            }

            return redirect()->route('products.index');
        }

        return back()->withErrors(['email' => 'Login gagal. Periksa kredensial.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
