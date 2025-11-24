<?php

namespace App\Services;

use App\Models\Part;
use App\Models\Supplier;

class DiscountService
{
    /**
     * Menghitung harga jual final.
     * Konsumen dihapus dari parameter wajib.
     */
    public function calculateSalesDiscount(Part $part, $konsumen = null, float $basePrice, float $manualDiscount = 0): array
    {
        if ($manualDiscount < 0) $manualDiscount = 0;
        if ($manualDiscount > $basePrice) $manualDiscount = $basePrice;

        $finalPrice = $basePrice - $manualDiscount;

        $steps = ['Harga Awal: ' . number_format($basePrice, 2)];
        if ($manualDiscount > 0) {
            $steps[] = 'Diskon Manual: -' . number_format($manualDiscount, 2);
        }
        
        return [
            'original_price' => $basePrice,
            'final_price' => $finalPrice,
            'discount_amount' => $manualDiscount,
            'applied_discounts' => $manualDiscount > 0 ? ['Manual'] : [],
            'calculation_steps' => $steps,
        ];
    }

    public function calculatePurchaseDiscount(Part $part, Supplier $supplier, float $basePrice): array
    {
        return [
            'original_price' => $basePrice,
            'final_price' => $basePrice,
            'applied_discounts' => [],
            'calculation_steps' => ['Harga Beli: ' . number_format($basePrice, 2)],
        ];
    }
}