<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rak extends Model
{
    use HasFactory;
    protected $fillable = [
        'gudang_id', 'kode_rak', 'nama_rak', 'is_active'
    ];

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }
}
