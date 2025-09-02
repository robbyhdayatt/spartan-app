<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturnDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function part() { return $this->belongsTo(Part::class); }
}
