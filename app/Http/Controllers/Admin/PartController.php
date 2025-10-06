<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PartsImport;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Pastikan ini ada

class PartController extends Controller
{
    /**
     * Terapkan middleware otentikasi ke semua metode di controller ini.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Menampilkan daftar semua part. Boleh diakses semua role yang login.
     */
    public function index()
    {
        $parts = Part::with(['brand', 'category'])->latest()->get();
        $brands = Brand::where('is_active', true)->orderBy('nama_brand')->get();
        $categories = Category::where('is_active', true)->orderBy('nama_kategori')->get();

        return view('admin.parts.index', compact('parts', 'brands', 'categories'));
    }

    /**
     * Menyimpan part baru. Hanya untuk Super Admin.
     */
    public function store(Request $request)
    {
        // Menggunakan Gate 'is-super-admin' yang sudah Anda definisikan
        $this->authorize('is-super-admin');

        $validated = $request->validate([
            'kode_part' => 'required|string|max:50|unique:parts',
            'nama_part' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'satuan' => 'required|string|max:20',
            'stok_minimum' => 'nullable|integer|min:0',
            'harga_beli_default' => 'required|numeric|min:0',
            'harga_jual_default' => 'required|numeric|min:0',
        ]);

        Part::create($validated);

        return redirect()->route('admin.parts.index')->with('success', 'Part berhasil ditambahkan!');
    }

    /**
     * Memperbarui part yang ada. Hanya untuk Super Admin.
     */
    public function update(Request $request, Part $part)
    {
        $this->authorize('is-super-admin');

        $validated = $request->validate([
            'kode_part' => 'required|string|max:50|unique:parts,kode_part,' . $part->id,
            'nama_part' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'satuan' => 'required|string|max:20',
            'stok_minimum' => 'nullable|integer|min:0',
            'harga_beli_default' => 'required|numeric|min:0',
            'harga_jual_default' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $part->update($validated);

        return redirect()->route('admin.parts.index')->with('success', 'Part berhasil diperbarui!');
    }

    /**
     * Menghapus part. Hanya untuk Super Admin.
     */
    public function destroy(Part $part)
    {
        $this->authorize('is-super-admin');

        // Tambahkan pengecekan relasi sebelum menghapus
        if ($part->inventoryBatches()->exists() || $part->penjualanDetails()->exists() || $part->purchaseOrderDetails()->exists()) {
            return redirect()->route('admin.parts.index')->with('error', 'Part tidak dapat dihapus karena sudah memiliki riwayat transaksi.');
        }

        $part->delete();
        return redirect()->route('admin.parts.index')->with('success', 'Part berhasil dihapus!');
    }

    /**
     * Mengimpor part dari file Excel. Hanya untuk Super Admin.
     */
    public function import(Request $request)
    {
        $this->authorize('is-super-admin');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new PartsImport, $request->file('file'));
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return back()->with('import_errors', $errorMessages);
        }

        return redirect()->route('admin.parts.index')->with('success', 'Data part berhasil diimpor.');
    }

    /**
     * API untuk pencarian part (digunakan di form lain). Boleh diakses semua role.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $parts = Part::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('nama_part', 'like', "%{$query}%")
                  ->orWhere('kode_part', 'like', "%{$query}%");
            })
            ->with(['brand', 'category'])
            ->limit(20)
            ->get();

        // Logika harga kampanye bisa disederhanakan atau dipisah jika kompleks
        return response()->json($parts->map(function($part) {
            return [
                'id' => $part->id,
                'nama_part' => $part->nama_part,
                'kode_part' => $part->kode_part,
                'harga_beli_default' => $part->harga_beli_default,
                'satuan' => $part->satuan,
                'brand_name' => optional($part->brand)->nama_brand,
                'category_name' => optional($part->category)->nama_kategori,
            ];
        }));
    }
}
