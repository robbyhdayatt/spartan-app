<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Part;
use App\Models\ReceivingDetail;
use App\Models\PurchaseOrderDetail;
use Illuminate\Support\Facades\DB;

class RecalculateAveragePurchasePrice extends Command
{
    protected $signature = 'recalculate:average-purchase-price {--part_id=}';

    protected $description = 'Recalculates the weighted average purchase price for parts based on their receiving history.';

    public function handle()
    {
        $this->info('Starting recalculation of average purchase prices...');

        $partId = $this->option('part_id');

        $partQuery = Part::query();

        if ($partId) {
            $this->info("Targeting specific part_id: {$partId}");
            $partQuery->where('id', $partId);
        } else {
            $this->info("Targeting all parts with stock...");
            $partQuery->whereHas('inventories', function ($query) {
                $query->where('quantity', '>', 0);
            });
        }

        $parts = $partQuery->get();

        if ($parts->isEmpty()) {
            $this->warn('No parts with stock found matching the criteria. Nothing to recalculate.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($parts->count());
        $progressBar->start();

        foreach ($parts as $part) {
            $this->line("\nProcessing Part ID: {$part->id} ({$part->nama_part})");

            $receivingDetails = ReceivingDetail::where('part_id', $part->id)
                ->where('qty_lolos_qc', '>', 0)
                ->with('receiving.purchaseOrder')
                ->get()
                ->sortBy('receiving.tanggal_terima');

            $totalValue = 0;
            $totalStock = 0;

            if ($receivingDetails->isEmpty()) {
                $this->warn(" -> No receiving history with 'qty_lolos_qc > 0' found. Falling back to default price.");
                $part->harga_beli_rata_rata = $part->harga_beli_default;
                $part->save();
                $progressBar->advance();
                continue;
            }

            $this->info(" -> Found {$receivingDetails->count()} receiving record(s). Calculating...");

            foreach ($receivingDetails as $detail) {
                $jumlahMasuk = $detail->qty_lolos_qc;

                if (!$detail->receiving || !$detail->receiving->purchaseOrder) {
                    $this->warn("   -> Skipping a receiving detail because PO link is missing.");
                    continue;
                }

                $poDetail = PurchaseOrderDetail::where('purchase_order_id', $detail->receiving->purchase_order_id)
                                               ->where('part_id', $part->id)
                                               ->first();

                // === PERBAIKAN DI SINI ===
                $hargaBeli = $poDetail ? $poDetail->harga_beli : $part->harga_beli_default;

                $this->info("   -> Found receiving: {$jumlahMasuk} pcs @ Rp " . number_format($hargaBeli));

                $totalValue += $jumlahMasuk * $hargaBeli;
                $totalStock += $jumlahMasuk;
            }

            if ($totalStock > 0) {
                $newAveragePrice = $totalValue / $totalStock;
                $part->harga_beli_rata_rata = $newAveragePrice;
                $part->save();
                $this->info(" -> SUCCESS: New average price is Rp " . number_format($newAveragePrice));
            } else {
                $this->warn(" -> WARNING: Total stock from history is zero. Cannot calculate. Using default price as fallback.");
                $part->harga_beli_rata_rata = $part->harga_beli_default;
                $part->save();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\n\nRecalculation complete for " . $parts->count() . " parts.");
        return 0;
    }
}
