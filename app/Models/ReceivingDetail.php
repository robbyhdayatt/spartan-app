<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivingDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Get the main receiving document that this detail belongs to.
     */
    public function receiving()
    {
        return $this->belongsTo(Receiving::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
