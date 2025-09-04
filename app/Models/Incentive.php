<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incentive extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function user() { return $this->belongsTo(User::class); }
    public function salesTarget() { return $this->belongsTo(SalesTarget::class); }
}
