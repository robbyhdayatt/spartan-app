<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockByWarehouseExport implements FromCollection, WithHeadings, WithMapping
{
    protected $gudang_id;

    public function __construct(int $gudang_id)
    {
        $this->gudang_id = $gudang_id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Inventory::where('gudang_id', $this->gudang_id)
            ->with(['part', 'rak', 'gudang'])
            ->where('quantity', '>', 0)
            ->get();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'Gudang',
            'Kode Part',
            'Nama Part',
            'Kode Rak',
            'Nama Rak',
            'Jumlah Stok',
        ];
    }

    /**
    * @param mixed $inventory
    * @return array
    */
    public function map($inventory): array
    {
        return [
            $inventory->gudang->nama_gudang,
            $inventory->part->kode_part,
            $inventory->part->nama_part,
            $inventory->rak->kode_rak,
            $inventory->rak->nama_rak,
            $inventory->quantity,
        ];
    }
}
