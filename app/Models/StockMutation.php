<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Method untuk generate nomor mutasi (jika belum ada)
    public static function generateMutationNumber()
    {
        $prefix = 'MUT/' . now()->format('Ymd') . '/';
        $lastMutation = self::where('nomor_mutasi', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $number = $lastMutation ? ((int)substr($lastMutation->nomor_mutasi, -4)) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function gudangAsal()
    {
        return $this->belongsTo(Gudang::class, 'gudang_asal_id');
    }

    public function gudangTujuan()
    {
        return $this->belongsTo(Gudang::class, 'gudang_tujuan_id');
    }

    public function rakAsal()
    {
        return $this->belongsTo(Rak::class, 'rak_asal_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
