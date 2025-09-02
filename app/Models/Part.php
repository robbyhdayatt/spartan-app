<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode_part', 'nama_part', 'satuan', 'stok_minimum', 'harga_beli_default',
        'harga_jual_default', 'foto_part', 'brand_id', 'category_id', 'is_active'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Add this relationship to connect a Part to its inventory records.
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
