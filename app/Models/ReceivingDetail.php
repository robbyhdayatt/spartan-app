<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivingDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    // TAMBAHKAN INI AGAR VALIDASI QC TIDAK ERROR
    protected $casts = [
        'qty_terima' => 'integer',
        'qty_lolos_qc' => 'integer',
        'qty_gagal_qc' => 'integer',
        'qty_disimpan' => 'integer',
    ];

    public function receiving()
    {
        return $this->belongsTo(Receiving::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function inventoryBatches()
    {
        return $this->hasMany(InventoryBatch::class);
    }
}