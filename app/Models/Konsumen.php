<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsumen extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode_konsumen', 'nama_konsumen', 'tipe_konsumen', 'alamat', 'telepon', 'is_active'
    ];

    public function customerDiscountCategories()
    {
        return $this->belongsToMany(CustomerDiscountCategory::class, 'customer_discount_category_konsumen');
    }
}
