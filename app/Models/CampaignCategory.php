<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Sebuah kategori diskon dimiliki oleh satu campaign utama.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Sebuah kategori diskon bisa berlaku untuk banyak konsumen.
     */
    public function konsumens()
    {
        return $this->belongsToMany(Konsumen::class, 'campaign_category_konsumen');
    }
}
