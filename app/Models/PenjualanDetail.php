<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Get the main sales transaction that this detail belongs to.
     */
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function rak()
    {
        return $this->belongsTo(Rak::class);
    }
}
