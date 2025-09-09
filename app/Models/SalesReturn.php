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

        public static function generateReturnNumber()
    {
        $prefix = 'RTJ/' . now()->format('Ymd') . '/';

        // Cari nomor retur terakhir untuk hari ini
        $lastReturn = self::where('nomor_retur_jual', 'like', $prefix . '%')
                          ->orderBy('nomor_retur_jual', 'desc')
                          ->first();

        $number = 1;
        if ($lastReturn) {
            // Ambil 4 digit terakhir, ubah ke integer, lalu tambah 1
            $lastNumber = (int) substr($lastReturn->nomor_retur_jual, -4);
            $number = $lastNumber + 1;
        }

        // Format nomor baru dengan padding 4 digit angka nol di depan
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
