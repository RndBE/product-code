<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    // Nama tabel (opsional, default = plural dari nama model: "products")
    protected $table = 'products';

    protected $guarded = [];
}
