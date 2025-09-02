<?php

namespace App\Exports;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryValueExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Inventory::with(['part', 'gudang', 'rak'])
            ->where('quantity', '>', 0)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Gudang',
            'Kode Part',
            'Nama Part',
            'Rak',
            'Stok Saat Ini',
            'Harga Beli Satuan (Rp)',
            'Subtotal Nilai (Rp)',
        ];
    }

    public function map($inventory): array
    {
        $subtotal = $inventory->quantity * $inventory->part->harga_beli_default;

        return [
            $inventory->gudang->nama_gudang,
            $inventory->part->kode_part,
            $inventory->part->nama_part,
            $inventory->rak->kode_rak,
            $inventory->quantity,
            $inventory->part->harga_beli_default,
            $subtotal,
        ];
    }
}
