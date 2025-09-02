<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Get the part that this inventory record belongs to.
     */
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Get the shelf where this inventory is located.
     */
    public function rak()
    {
        return $this->belongsTo(Rak::class);
    }
    /**
     * Get the warehouse where this inventory is stored.
     */
    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }
}
