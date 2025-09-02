<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::latest()->get();
        return view('admin.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'nama_brand' => 'required|string|max:100|unique:brands',
        ]);

        Brand::create($validated);

        return redirect()->route('admin.brands.index')->with('success', 'Brand berhasil ditambahkan!');
    }

    public function update(Request $request, Brand $brand)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'nama_brand' => 'required|string|max:100|unique:brands,nama_brand,' . $brand->id,
            'is_active' => 'required|boolean',
        ]);

        $brand->update($validated);

        return redirect()->route('admin.brands.index')->with('success', 'Brand berhasil diperbarui!');
    }

    public function destroy(Brand $brand)
    {
        $this->authorize('is-super-admin');
        $brand->delete();
        return redirect()->route('admin.brands.index')->with('success', 'Brand berhasil dihapus!');
    }
}
