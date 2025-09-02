<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category; // Ganti menjadi model Category
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get(); // Ganti variabel
        return view('admin.categories.index', compact('categories')); // Ganti view dan variabel
    }

    public function store(Request $request)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:categories', // Ganti field dan tabel
        ]);

        Category::create($validated); // Ganti model

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan!'); // Ganti route
    }

    public function update(Request $request, Category $category) // Ganti model
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:categories,nama_kategori,' . $category->id, // Ganti field dan tabel
            'is_active' => 'required|boolean',
        ]);

        $category->update($validated); // Ganti variabel

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui!'); // Ganti route
    }

    public function destroy(Category $category) // Ganti model
    {
        $this->authorize('is-super-admin');
        $category->delete(); // Ganti variabel
        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus!'); // Ganti route
    }
}
