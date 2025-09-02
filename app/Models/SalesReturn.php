<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = ['tanggal_retur' => 'date'];

    public function details() { return $this->hasMany(SalesReturnDetail::class); }
    public function penjualan() { return $this->belongsTo(Penjualan::class); }
    public function konsumen() { return $this->belongsTo(Konsumen::class); }
    public function gudang() { return $this->belongsTo(Gudang::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
