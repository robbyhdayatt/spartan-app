<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gudang;
use Illuminate\Http\Request;

class GudangController extends Controller
{
    public function index()
    {
        $gudangs = Gudang::latest()->get();
        return view('admin.gudangs.index', compact('gudangs'));
    }

    public function store(Request $request)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'kode_gudang' => 'required|string|max:10|unique:gudangs',
            'nama_gudang' => 'required|string|max:100',
            'alamat' => 'nullable|string',
        ]);

        Gudang::create($validated);

        return redirect()->route('admin.gudangs.index')->with('success', 'Gudang berhasil ditambahkan!');
    }

    public function update(Request $request, Gudang $gudang)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'kode_gudang' => 'required|string|max:10|unique:gudangs,kode_gudang,' . $gudang->id,
            'nama_gudang' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $gudang->update($validated);

        return redirect()->route('admin.gudangs.index')->with('success', 'Gudang berhasil diperbarui!');
    }

    public function destroy(Gudang $gudang)
    {
        $this->authorize('is-super-admin');
        $gudang->delete();
        return redirect()->route('admin.gudangs.index')->with('success', 'Gudang berhasil dihapus!');
    }
}
