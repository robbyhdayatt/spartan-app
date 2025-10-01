<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerDiscountCategory;
use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerDiscountCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        $categories = CustomerDiscountCategory::withCount('konsumens')->latest()->get();
        return view('admin.customer_discount_categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create(): View
    {
        // --- LOGIKA BARU ---
        // 1. Ambil semua ID konsumen yang sudah terdaftar di kategori manapun.
        $assignedKonsumenIds = DB::table('customer_discount_category_konsumen')->pluck('konsumen_id');

        // 2. Ambil semua konsumen yang aktif DAN yang ID-nya TIDAK ADA di daftar yang sudah terdaftar.
        $konsumens = Konsumen::where('is_active', true)
            ->whereNotIn('id', $assignedKonsumenIds)
            ->orderBy('nama_konsumen')
            ->get();
        // --- END LOGIKA BARU ---

        return view('admin.customer_discount_categories.create', compact('konsumens'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:customer_discount_categories',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'konsumen_ids' => 'nullable|array',
            'konsumen_ids.*' => 'exists:konsumens,id',
        ]);

        // Validasi ini tetap penting sebagai pengaman di sisi server
        if ($request->has('konsumen_ids')) {
            foreach ($request->konsumen_ids as $konsumen_id) {
                $existing = DB::table('customer_discount_category_konsumen')->where('konsumen_id', $konsumen_id)->first();
                if ($existing) {
                    $konsumen = Konsumen::find($konsumen_id);
                    $category = CustomerDiscountCategory::find($existing->customer_discount_category_id);
                    return back()->with('error', "Gagal! Konsumen '{$konsumen->nama_konsumen}' sudah terdaftar di kategori '{$category->nama_kategori}'.")->withInput();
                }
            }
        }

        DB::beginTransaction();
        try {
            $category = CustomerDiscountCategory::create($request->except('konsumen_ids'));
            if ($request->has('konsumen_ids')) {
                $category->konsumens()->attach($request->konsumen_ids);
            }
            DB::commit();
            return redirect()->route('admin.customer-discount-categories.index')->with('success', 'Kategori diskon berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerDiscountCategory  $customerDiscountCategory
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(CustomerDiscountCategory $customerDiscountCategory): View
    {
        // --- LOGIKA BARU ---
        // 1. Ambil semua ID konsumen yang sudah terdaftar di kategori LAIN.
        $assignedKonsumenIdsInOtherCategories = DB::table('customer_discount_category_konsumen')
            ->where('customer_discount_category_id', '!=', $customerDiscountCategory->id)
            ->pluck('konsumen_id');

        // 2. Ambil semua konsumen yang aktif DAN yang ID-nya TIDAK ADA di daftar tersebut.
        //    Ini akan menampilkan konsumen yang belum punya kategori + konsumen yang sudah ada di kategori ini.
        $konsumens = Konsumen::where('is_active', true)
            ->whereNotIn('id', $assignedKonsumenIdsInOtherCategories)
            ->orderBy('nama_konsumen')
            ->get();
        // --- END LOGIKA BARU ---

        $selectedKonsumens = $customerDiscountCategory->konsumens->pluck('id')->toArray();

        return view('admin.customer_discount_categories.edit', compact('customerDiscountCategory', 'konsumens', 'selectedKonsumens'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerDiscountCategory  $customerDiscountCategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, CustomerDiscountCategory $customerDiscountCategory): RedirectResponse
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:customer_discount_categories,nama_kategori,' . $customerDiscountCategory->id,
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'konsumen_ids' => 'nullable|array',
            'konsumen_ids.*' => 'exists:konsumens,id',
        ]);

        // Validasi ini tetap penting sebagai pengaman di sisi server
        if ($request->has('konsumen_ids')) {
            foreach ($request->konsumen_ids as $konsumen_id) {
                $existing = DB::table('customer_discount_category_konsumen')
                    ->where('konsumen_id', $konsumen_id)
                    ->where('customer_discount_category_id', '!=', $customerDiscountCategory->id)
                    ->first();
                if ($existing) {
                    $konsumen = Konsumen::find($konsumen_id);
                    $category = CustomerDiscountCategory::find($existing->customer_discount_category_id);
                    return back()->with('error', "Gagal! Konsumen '{$konsumen->nama_konsumen}' sudah terdaftar di kategori '{$category->nama_kategori}'.")->withInput();
                }
            }
        }

        DB::beginTransaction();
        try {
            $customerDiscountCategory->update($request->except('konsumen_ids'));
            $customerDiscountCategory->konsumens()->sync($request->konsumen_ids ?? []);
            DB::commit();
            return redirect()->route('admin.customer-discount-categories.index')->with('success', 'Kategori diskon berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerDiscountCategory  $customerDiscountCategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(CustomerDiscountCategory $customerDiscountCategory): RedirectResponse
    {
        $customerDiscountCategory->delete();
        return redirect()->route('admin.customer-discount-categories.index')->with('success', 'Kategori diskon berhasil dihapus.');
    }
}
