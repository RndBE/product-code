<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('content')->nullable()->after('qr_code');
            $table->string('warranty_card')->nullable()->after('content');
            $table->integer('produk_jadi_list_id')->nullable()->after('warranty_card');
            $table->integer('produk_jadi_id')->nullable()->after('produk_jadi_list_id');
            $table->string('serial_number')->nullable()->after('produk_jadi_id');
            $table->string('nama_produk')->nullable()->after('serial_number');
            $table->string('kode_list')->nullable()->after('nama_produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            //
        });
    }
};
