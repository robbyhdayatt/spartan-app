<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Receiving extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_terima' => 'date',
        'qc_at' => 'datetime',
        'putaway_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function details()
    {
        return $this->hasMany(ReceivingDetail::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function qcBy()
    {
        return $this->belongsTo(User::class, 'qc_by');
    }

    public function putawayBy()
    {
        return $this->belongsTo(User::class, 'putaway_by');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'referensi');
    }

    public static function generateReceivingNumber()
    {
        $date = now()->format('Ymd');
        $latest = self::whereDate('created_at', today())->count();
        $sequence = str_pad($latest + 1, 4, '0', STR_PAD_LEFT);
        return "RCV/{$date}/{$sequence}";
    }
}
