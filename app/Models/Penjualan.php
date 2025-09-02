<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Tambahkan properti casts ini untuk menangani tanggal secara otomatis.
     */
    protected $casts = [
        'tanggal_jual' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class);
    }

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class);
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }
}
