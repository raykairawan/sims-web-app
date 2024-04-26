<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'nama_produk',
        'kategori_produk',
        'harga_beli',
        'harga_jual',
        'stok_produk',
        'image',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'kategori_produk', 'id');
    }
}
