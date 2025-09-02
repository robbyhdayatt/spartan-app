<?php

namespace App\Exports;

use App\Models\ReceivingDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchaseJournalExport implements FromCollection, WithHeadings, WithMapping
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
        return ReceivingDetail::with(['receiving.purchaseOrder.supplier', 'part'])
            ->whereHas('receiving', function ($query) {
                $query->whereBetween('tanggal_terima', [$this->startDate, $this->endDate]);
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal Terima',
            'Nomor Penerimaan',
            'Nomor PO',
            'Nama Supplier',
            'Kode Part',
            'Nama Part',
            'Qty Diterima',
            'Harga Beli',
            'Subtotal',
        ];
    }

    public function map($detail): array
    {
        // Calculate subtotal for export
        $harga_beli = $detail->receiving->purchaseOrder->details->firstWhere('part_id', $detail->part_id)->harga_beli ?? 0;
        $subtotal = $detail->qty_terima * $harga_beli;

        return [
            $detail->receiving->tanggal_terima->format('d-m-Y'),
            $detail->receiving->nomor_penerimaan,
            $detail->receiving->purchaseOrder->nomor_po,
            $detail->receiving->purchaseOrder->supplier->nama_supplier,
            $detail->part->kode_part,
            $detail->part->nama_part,
            $detail->qty_terima,
            $harga_beli,
            $subtotal,
        ];
    }
}
