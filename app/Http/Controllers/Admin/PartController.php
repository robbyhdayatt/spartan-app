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

class PartController extends Controller
{
    public function index()
    {
        $parts = Part::with(['brand', 'category'])->latest()->get();
        $brands = Brand::where('is_active', true)->orderBy('nama_brand')->get();
        $categories = Category::where('is_active', true)->orderBy('nama_kategori')->get();

        return view('admin.parts.index', compact('parts', 'brands', 'categories'));
    }

    public function store(Request $request)
    {
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

    public function destroy(Part $part)
    {
        $this->authorize('is-super-admin');
        $part->delete();
        return redirect()->route('admin.parts.index')->with('success', 'Part berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $this->authorize('is-super-admin'); // Pastikan hanya admin yang bisa import

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
public function search(Request $request)
{
    $query = $request->get('q');
    $page = $request->get('page', 1);
    $perPage = 20;

    // Jika tidak ada query, return empty
    if (empty($query)) {
        return response()->json([]);
    }

    // Build query dengan filtering yang lebih baik
    $partsQuery = \App\Models\Part::where('is_active', true)
        ->where(function($q) use ($query) {
            $q->where('nama_part', 'like', "%{$query}%")
              ->orWhere('kode_part', 'like', "%{$query}%");
        })
        ->with(['brand', 'category']); // Include relations untuk informasi lengkap

    // Get paginated results
    $parts = $partsQuery
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

    return response()->json($parts->map(function($part) {
        return [
            'id' => $part->id,
            'nama_part' => $part->nama_part,
            'kode_part' => $part->kode_part,
            'effective_price' => $part->harga_beli_default,
            'satuan' => $part->satuan,
            'brand_name' => $part->brand ? $part->brand->nama_brand : '',
            'category_name' => $part->category ? $part->category->nama_kategori : '',
        ];
    }));
}
}
