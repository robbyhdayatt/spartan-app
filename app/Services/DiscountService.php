<?php

namespace App\Services;

use App\Models\Part;
use App\Models\Konsumen;
use App\Models\Campaign;
use App\Models\Supplier;
use Carbon\Carbon;

class DiscountService
{
    /**
     * Menghitung harga jual final setelah semua diskon campaign diterapkan.
     *
     * @param Part $part
     * @param Konsumen $konsumen
     * @param float $basePrice
     * @return array
     */
    public function calculateSalesDiscount(Part $part, Konsumen $konsumen, float $basePrice): array
    {
        $today = Carbon::today();
        $applicableDiscounts = collect();
        $finalPrice = $basePrice;

        // 1. Cari semua campaign Penjualan yang aktif dan relevan
        $activeCampaigns = Campaign::where('tipe', 'PENJUALAN')
            ->where('is_active', true)
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->with(['parts', 'categories.konsumens']) // Eager load relasi untuk performa
            ->get();

        foreach ($activeCampaigns as $campaign) {
            // Cek apakah campaign ini berlaku untuk part ini
            // Jika relasi 'parts' kosong, artinya berlaku untuk semua part.
            // Jika tidak kosong, cek apakah part_id ada di dalamnya.
            $partIsEligible = $campaign->parts->isEmpty() || $campaign->parts->contains($part->id);

            if ($partIsEligible) {
                // Tambahkan diskon utama dari campaign
                $applicableDiscounts->push([
                    'name' => $campaign->nama_campaign,
                    'percentage' => $campaign->discount_percentage,
                ]);

                // Cek kategori diskon tambahan
                foreach ($campaign->categories as $category) {
                    // Cek apakah konsumen ini termasuk dalam kategori
                    if ($category->konsumens->contains($konsumen->id)) {
                        $applicableDiscounts->push([
                            'name' => $category->nama_kategori,
                            'percentage' => $category->discount_percentage,
                        ]);
                    }
                }
            }
        }

        // 2. Terapkan diskon secara berantai (multiplicative)
        $appliedSteps = [];
        $discounts = $applicableDiscounts->where('percentage', '>', 0)->unique('name');

        foreach ($discounts as $discount) {
            $discountAmount = $finalPrice * ($discount['percentage'] / 100);
            $finalPrice -= $discountAmount;
            $appliedSteps[] = "Diskon '{$discount['name']}' ({$discount['percentage']}%) diterapkan. Harga menjadi: " . number_format($finalPrice, 2);
        }

        // 3. Kembalikan hasil kalkulasi
        return [
            'original_price' => $basePrice,
            'final_price' => $finalPrice,
            'total_discount_percentage' => $discounts->sum('percentage'), // Ini hanya untuk tampilan, bukan untuk kalkulasi
            'applied_discounts' => $discounts->pluck('name')->all(),
            'calculation_steps' => $appliedSteps,
        ];
    }

    /**
     * Menghitung harga beli final setelah diskon campaign diterapkan.
     *
     * @param Part $part
     * @param Supplier $supplier
     * @param float $basePrice
     * @return array
     */
    public function calculatePurchaseDiscount(Part $part, Supplier $supplier, float $basePrice): array
    {
        $today = Carbon::today();
        $applicableDiscounts = collect();
        $finalPrice = $basePrice;

        // 1. Cari semua campaign Pembelian yang aktif dan relevan
        $activeCampaigns = Campaign::where('tipe', 'PEMBELIAN')
            ->where('is_active', true)
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->with(['parts', 'suppliers']) // Eager load relasi
            ->get();

        foreach ($activeCampaigns as $campaign) {
            // Cek apakah campaign berlaku untuk part ini
            $partIsEligible = $campaign->parts->isEmpty() || $campaign->parts->contains($part->id);

            // Cek apakah campaign berlaku untuk supplier ini
            $supplierIsEligible = $campaign->suppliers->isEmpty() || $campaign->suppliers->contains($supplier->id);

            if ($partIsEligible && $supplierIsEligible) {
                $applicableDiscounts->push([
                    'name' => $campaign->nama_campaign,
                    'percentage' => $campaign->discount_percentage,
                ]);
            }
        }

        // 2. Terapkan diskon (hanya satu, yang terbesar, karena pembelian biasanya tidak ada diskon ganda)
        // Jika Anda ingin diskon pembelian juga berantai, ganti logika ini dengan yang ada di calculateSalesDiscount
        $bestDiscount = $applicableDiscounts->where('percentage', '>', 0)->sortByDesc('percentage')->first();
        $appliedSteps = [];
        $appliedDiscounts = [];

        if ($bestDiscount) {
            $discountAmount = $finalPrice * ($bestDiscount['percentage'] / 100);
            $finalPrice -= $discountAmount;
            $appliedDiscounts[] = $bestDiscount['name'];
            $appliedSteps[] = "Diskon terbaik '{$bestDiscount['name']}' ({$bestDiscount['percentage']}%) diterapkan. Harga menjadi: " . number_format($finalPrice, 2);
        }

        // 3. Kembalikan hasil kalkulasi
        return [
            'original_price' => $basePrice,
            'final_price' => $finalPrice,
            'applied_discounts' => $appliedDiscounts,
            'calculation_steps' => $appliedSteps,
        ];
    }
}
