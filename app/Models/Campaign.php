<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'discount_percentage' => 'float', // Tambahkan ini
    ];

    /**
     * Hapus relasi part() yang lama karena sudah tidak relevan.
     * public function part() { ... }
     */

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // --- RELASI BARU ---

    /**
     * Sebuah campaign bisa memiliki banyak kategori diskon (untuk tipe PENJUALAN).
     */
    public function categories()
    {
        return $this->hasMany(CampaignCategory::class);
    }

    /**
     * Sebuah campaign bisa berlaku untuk banyak part.
     */
    public function parts()
    {
        return $this->belongsToMany(Part::class, 'campaign_part');
    }

    /**
     * Sebuah campaign bisa berlaku untuk banyak supplier (untuk tipe PEMBELIAN).
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'campaign_supplier');
    }
}
