<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Penjualan;
use PDF;

class PdfController extends Controller
{
    public function purchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'gudang', 'details.part', 'createdBy']);
        $data = ['purchaseOrder' => $purchaseOrder];

        // Ukuran standar 9.5 x 5.5 inci (landscape)
        $pdf = PDF::loadView('admin.purchase_orders.print', $data)
                    ->setPaper([0, 0, 396, 684], 'landscape');

        return $pdf->download('PO-' . $purchaseOrder->nomor_po . '.pdf');
    }

    public function penjualan(Penjualan $penjualan)
    {
        $penjualan->load(['konsumen', 'gudang', 'sales', 'details.part']);
        $data = ['penjualan' => $penjualan];

        // Ukuran standar 9.5 x 5.5 inci (landscape)
        $pdf = PDF::loadView('admin.penjualans.print', $data)
                    ->setPaper([0, 0, 396, 684], 'landscape');

        return $pdf->download('Faktur-' . $penjualan->nomor_faktur . '.pdf');
    }
}
