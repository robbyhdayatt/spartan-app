<?php

namespace App\Exports;

use App\Models\PenjualanDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesJournalExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return PenjualanDetail::with(['penjualan.konsumen', 'penjualan.sales', 'part'])
            ->whereHas('penjualan', function ($query) {
                $query->whereBetween('tanggal_jual', [$this->startDate, $this->endDate]);
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nomor Faktur',
            'Nama Konsumen',
            'Sales',
            'Kode Part',
            'Nama Part',
            'Qty',
            'Harga Jual',
            'Subtotal',
        ];
    }

    public function map($detail): array
    {
        return [
            $detail->penjualan->tanggal_jual->format('d-m-Y'),
            $detail->penjualan->nomor_faktur,
            $detail->penjualan->konsumen->nama_konsumen,
            $detail->penjualan->sales->nama ?? 'N/A',
            $detail->part->kode_part,
            $detail->part->nama_part,
            $detail->qty_jual,
            $detail->harga_jual,
            $detail->subtotal,
        ];
    }
}
