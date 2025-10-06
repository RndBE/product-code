<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CKEditorController;
use App\Http\Controllers\User\UserManualController;
use App\Http\Controllers\User\ProductVerificationController;


Route::get('/', [ProductVerificationController::class, 'index'])->name('home');
Route::get('/verify', [ProductVerificationController::class, 'index'])->name('verify.index');
Route::post('/verify', [ProductVerificationController::class, 'verify'])->name('verify.store');
// routes/web.php
Route::get('/captcha', [ProductVerificationController::class, 'generate'])->name('captcha.generate');

Route::get('/manual/{slug}', [UserManualController::class, 'show'])->name('user.manual');


Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:3,1')->name('admin.login.submit');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Proteksi route admin: pakai session auth + custom isAdmin
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->group(function () {

    // pastikan didefinisikan sebelum {id}
    Route::post('products/bulk-print', [ProductController::class, 'bulkPrint'])
        ->name('products.bulkPrint');

    // Index
    Route::get('products', [ProductController::class, 'index'])
        ->name('products.index');

    // Create form
    Route::get('products/create', [ProductController::class, 'create'])
        ->name('products.create');

    // Store
    Route::post('products', [ProductController::class, 'store'])
        ->name('products.store');

    // Show detail
    Route::get('products/{product}', [ProductController::class, 'show'])
        ->name('products.show');

    // Edit form
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])
        ->name('products.edit');

    // Update
    Route::put('products/{product}', [ProductController::class, 'update'])
        ->name('products.update');
    Route::patch('products/{product}', [ProductController::class, 'update']);

    // Destroy
    Route::delete('products/{product}', [ProductController::class, 'destroy'])
        ->name('products.destroy');
});








