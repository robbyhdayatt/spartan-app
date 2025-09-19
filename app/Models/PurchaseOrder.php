<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_po' => 'date',
        'approved_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    // FUNGSI UNTUK TAMPILAN STATUS (WARNA BADGE)
    public function getStatusClassAttribute()
    {
        switch ($this->status) {
            case 'PENDING_APPROVAL':
                return 'badge-warning';
            case 'APPROVED':
                return 'badge-success';
            case 'REJECTED':
                return 'badge-danger';
            case 'COMPLETED':
                return 'badge-primary';
            default:
                return 'badge-secondary';
        }
    }

    // FUNGSI UNTUK TAMPILAN STATUS (TEKS)
    public function getStatusBadgeAttribute()
    {
        return str_replace('_', ' ', $this->status);
    }
}
