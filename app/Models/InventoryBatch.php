<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function rak()
    {
        return $this->belongsTo(Rak::class);
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function receivingDetail()
    {
        return $this->belongsTo(ReceivingDetail::class);
    }
}
