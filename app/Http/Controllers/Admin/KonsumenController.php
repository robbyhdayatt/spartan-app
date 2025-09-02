<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Konsumen;
use Illuminate\Http\Request;

class KonsumenController extends Controller
{
    public function index()
    {
        $konsumens = Konsumen::latest()->paginate(10);
        return view('admin.konsumens.index', compact('konsumens'));
    }

    public function store(Request $request)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'kode_konsumen' => 'required|string|max:20|unique:konsumens',
            'nama_konsumen' => 'required|string|max:255',
            'tipe_konsumen' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
        ]);

        Konsumen::create($validated);

        return redirect()->route('admin.konsumens.index')->with('success', 'Konsumen berhasil ditambahkan!');
    }

    public function update(Request $request, Konsumen $konsumen)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'kode_konsumen' => 'required|string|max:20|unique:konsumens,kode_konsumen,' . $konsumen->id,
            'nama_konsumen' => 'required|string|max:255',
            'tipe_konsumen' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        $konsumen->update($validated);

        return redirect()->route('admin.konsumens.index')->with('success', 'Konsumen berhasil diperbarui!');
    }

    public function destroy(Konsumen $konsumen)
    {
        $this->authorize('is-super-admin');
        $konsumen->delete();
        return redirect()->route('admin.konsumens.index')->with('success', 'Konsumen berhasil dihapus!');
    }
}
