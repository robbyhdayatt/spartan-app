<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockCardExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $part_id;
    protected $gudang_id;
    protected $start_date;
    protected $end_date;

    public function __construct($part_id, $gudang_id, $start_date, $end_date)
    {
        $this->part_id = $part_id;
        $this->gudang_id = $gudang_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = StockMovement::where('part_id', $this->part_id)
            ->with(['part', 'gudang', 'user'])
            ->whereDate('created_at', '>=', $this->start_date)
            ->whereDate('created_at', '<=', $this->end_date);

        if ($this->gudang_id) {
            $query->where('gudang_id', $this->gudang_id);
        }

        return $query->oldest()->get();
    }

    public function headings(): array
    {
        return [
            'Part',
            'Tanggal',
            'Gudang',
            'Tipe Gerakan',
            'Jumlah',
            'Stok Sebelum',
            'Stok Sesudah',
            'Referensi',
            'User',
        ];
    }

    public function map($movement): array
    {
        return [
            $movement->part->nama_part . ' (' . $movement->part->kode_part . ')',
            $movement->created_at->format('d-m-Y H:i'),
            $movement->gudang->nama_gudang ?? 'N/A',
            str_replace('_', ' ', $movement->tipe_gerakan),
            $movement->jumlah,
            $movement->stok_sebelum,
            $movement->stok_sesudah,
            $movement->referensi,
            $movement->user->nama ?? 'Sistem',
        ];
    }
}
