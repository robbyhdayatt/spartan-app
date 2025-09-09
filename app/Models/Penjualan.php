<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static function generateNomorFaktur() // Nama fungsi diubah
    {
        $prefix = 'INV/' . date('Ym') . '/';

        // PERBAIKAN: Menggunakan kolom 'nomor_faktur'
        $lastSale = DB::table('penjualans')
                      ->where('nomor_faktur', 'like', $prefix . '%')
                      ->orderBy('nomor_faktur', 'desc')
                      ->first();

        if ($lastSale) {
            // PERBAIKAN: Mengambil dari kolom 'nomor_faktur'
            $lastNumber = (int) substr($lastSale->nomor_faktur, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'referensi');
    }
}
