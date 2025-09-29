<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerDiscountCategory;
use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View; // Import View
use Illuminate\Http\RedirectResponse; // Import RedirectResponse

class CustomerDiscountCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        // Ambil semua kategori dengan jumlah konsumen terkait
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
        $konsumens = Konsumen::where('is_active', true)->orderBy('nama_konsumen')->get();
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
        $konsumens = Konsumen::where('is_active', true)->orderBy('nama_konsumen')->get();
        // Ambil ID konsumen yang sudah terhubung dengan kategori ini
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

        DB::beginTransaction();
        try {
            $customerDiscountCategory->update($request->except('konsumen_ids'));

            // Gunakan sync untuk memperbarui relasi, ini akan otomatis menambah/menghapus yang perlu
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