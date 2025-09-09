<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receiving extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Add this casts property to automatically handle the date.
     */
    protected $casts = [
        'tanggal_terima' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(ReceivingDetail::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }



    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

        public function stockMovements()
    {
        // Relasi 'morphMany' digunakan karena stock_movements bisa berelasi
        // ke banyak model lain (Penjualan, Adjustment, dll)
        return $this->morphMany(StockMovement::class, 'referensi');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
