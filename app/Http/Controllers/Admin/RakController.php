<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gudang;
use App\Models\Rak;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RakController extends Controller
{
    public function index()
    {
        // Ambil data rak dengan relasi gudang (Eager Loading)
        $raks = Rak::with('gudang')->latest()->get();
        // Ambil data gudang yang aktif untuk form dropdown
        $gudangs = Gudang::where('is_active', true)->orderBy('nama_gudang')->get();

        return view('admin.raks.index', compact('raks', 'gudangs'));
    }

    public function store(Request $request)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'nama_rak' => 'required|string|max:100',
            // Pastikan kode rak unik untuk setiap gudang
            'kode_rak' => [
                'required',
                'string',
                'max:20',
                Rule::unique('raks')->where(function ($query) use ($request) {
                    return $query->where('gudang_id', $request->gudang_id);
                }),
            ],
        ]);

        Rak::create($validated);

        return redirect()->route('admin.raks.index')->with('success', 'Rak berhasil ditambahkan!');
    }

    public function update(Request $request, Rak $rak)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'gudang_id' => 'required|exists:gudangs,id',
            'nama_rak' => 'required|string|max:100',
            'kode_rak' => [
                'required',
                'string',
                'max:20',
                Rule::unique('raks')->where(function ($query) use ($request) {
                    return $query->where('gudang_id', $request->gudang_id);
                })->ignore($rak->id),
            ],
            'is_active' => 'required|boolean',
        ]);

        $rak->update($validated);

        return redirect()->route('admin.raks.index')->with('success', 'Rak berhasil diperbarui!');
    }

    public function destroy(Rak $rak)
    {
        $this->authorize('is-super-admin');
        $rak->delete();
        return redirect()->route('admin.raks.index')->with('success', 'Rak berhasil dihapus!');
    }
}
