<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDiscountCategory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function konsumens()
    {
        return $this->belongsToMany(Konsumen::class, 'customer_discount_category_konsumen');
    }
}
