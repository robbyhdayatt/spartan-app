<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Part;
use App\Models\Supplier;
use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    // ... (fungsi index() dan store() yang sudah ada tetap sama) ...
    public function index()
    {
        $campaigns = Campaign::with(['parts', 'suppliers'])->latest()->get();
        $parts = Part::where('is_active', true)->orderBy('nama_part')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();
        $konsumens = Konsumen::where('is_active', true)->orderBy('nama_konsumen')->get();
        return view('admin.campaigns.index', compact('campaigns', 'parts', 'suppliers', 'konsumens'));
    }

    public function store(Request $request)
    {
        $this->authorize('is-manager');
        $validated = $request->validate([
            'nama_campaign' => 'required|string|max:255',
            'tipe' => 'required|in:PENJUALAN,PEMBELIAN',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'applies_to_all_parts' => 'required|boolean',
            'part_ids' => 'required_if:applies_to_all_parts,false|array',
            'part_ids.*' => 'exists:parts,id',
        ]);
        DB::beginTransaction();
        try {
            $campaign = Campaign::create([
                'nama_campaign' => $validated['nama_campaign'],
                'tipe' => $validated['tipe'],
                'discount_percentage' => $validated['discount_percentage'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'created_by' => Auth::id(),
            ]);

            if ($validated['tipe'] === 'PEMBELIAN') {
                $request->validate([
                    'applies_to_all_suppliers' => 'required|boolean',
                    'supplier_ids' => 'required_if:applies_to_all_suppliers,false|array',
                    'supplier_ids.*' => 'exists:suppliers,id',
                ]);
                if (!$request->applies_to_all_suppliers) {
                    $campaign->suppliers()->attach($request->supplier_ids);
                }
            }

            if (!$validated['applies_to_all_parts']) {
                $campaign->parts()->attach($validated['part_ids']);
            }

            if ($validated['tipe'] === 'PENJUALAN' && $request->has('categories')) {
                foreach ($request->categories as $categoryData) {
                    $category = $campaign->categories()->create([
                        'nama_kategori' => $categoryData['nama'],
                        'discount_percentage' => $categoryData['diskon'],
                    ]);
                    if (!empty($categoryData['konsumen_ids'])) {
                        $category->konsumens()->attach($categoryData['konsumen_ids']);
                    }
                }
            }
            DB::commit();
            return redirect()->route('admin.campaigns.index')->with('success', 'Campaign baru berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit campaign.
     */
    public function edit(Campaign $campaign)
    {
        $this->authorize('is-manager');

        // Eager load semua relasi yang akan ditampilkan di form
        $campaign->load(['parts', 'suppliers', 'categories.konsumens']);

        // Ambil semua data master untuk pilihan dropdown
        $parts = Part::where('is_active', true)->orderBy('nama_part')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();
        $konsumens = Konsumen::where('is_active', true)->orderBy('nama_konsumen')->get();

        return view('admin.campaigns.edit', compact('campaign', 'parts', 'suppliers', 'konsumens'));
    }

    /**
     * Memperbarui data campaign di database.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $this->authorize('is-manager');

        // Validasi sama seperti store, ditambah status 'is_active'
        $validated = $request->validate([
            'nama_campaign' => 'required|string|max:255',
            'is_active' => 'required|boolean', // Tambahan
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'applies_to_all_parts' => 'required|boolean',
            'part_ids' => 'required_if:applies_to_all_parts,false|array',
            'part_ids.*' => 'exists:parts,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. Update data utama di tabel 'campaigns'
            $campaign->update([
                'nama_campaign' => $validated['nama_campaign'],
                'is_active' => $validated['is_active'],
                'discount_percentage' => $validated['discount_percentage'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
            ]);

            // 2. Update relasi part menggunakan sync()
            if ($validated['applies_to_all_parts']) {
                $campaign->parts()->detach(); // Hapus semua relasi jika berlaku untuk semua
            } else {
                $campaign->parts()->sync($validated['part_ids']);
            }

            // 3. Update relasi supplier untuk tipe PEMBELIAN
            if ($campaign->tipe === 'PEMBELIAN') {
                $request->validate([
                    'applies_to_all_suppliers' => 'required|boolean',
                    'supplier_ids' => 'required_if:applies_to_all_suppliers,false|array',
                    'supplier_ids.*' => 'exists:suppliers,id',
                ]);

                if ($request->applies_to_all_suppliers) {
                    $campaign->suppliers()->detach();
                } else {
                    $campaign->suppliers()->sync($request->supplier_ids);
                }
            }

            // 4. Update kategori diskon untuk tipe PENJUALAN
            if ($campaign->tipe === 'PENJUALAN') {
                // Hapus semua kategori lama dan relasinya
                $campaign->categories()->delete();

                // Buat ulang kategori dari data form yang baru
                if ($request->has('categories')) {
                    foreach ($request->categories as $categoryData) {
                        $category = $campaign->categories()->create([
                            'nama_kategori' => $categoryData['nama'],
                            'discount_percentage' => $categoryData['diskon'],
                        ]);

                        if (!empty($categoryData['konsumen_ids'])) {
                            $category->konsumens()->attach($categoryData['konsumen_ids']);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.campaigns.index')->with('success', 'Campaign berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus campaign.
     */
    public function destroy(Campaign $campaign)
    {
        $this->authorize('is-manager');
        $campaign->delete(); // onDelete('cascade') akan menghapus semua relasi di tabel pivot
        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign berhasil dihapus.');
    }
}
