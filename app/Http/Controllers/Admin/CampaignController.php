<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('part')->latest()->get();
        $parts = Part::where('is_active', true)->orderBy('nama_part')->get();
        return view('admin.campaigns.index', compact('campaigns', 'parts'));
    }

    public function store(Request $request)
    {
        $this->authorize('is-manager');
        $validated = $request->validate([
            'nama_campaign' => 'required|string|max:255',
            'part_id' => 'required|exists:parts,id',
            'harga_promo' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tipe' => 'required|in:PENJUALAN,PEMBELIAN',
        ]);

        Campaign::create($validated + ['created_by' => Auth::id()]);

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign berhasil ditambahkan.');
    }

    public function update(Request $request, Campaign $campaign)
    {
        $this->authorize('is-manager');
        $validated = $request->validate([
            'nama_campaign' => 'required|string|max:255',
            'part_id' => 'required|exists:parts,id',
            'harga_promo' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'is_active' => 'required|boolean',
            'tipe' => 'required|in:PENJUALAN,PEMBELIAN',
        ]);

        $campaign->update($validated);

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign berhasil diperbarui.');
    }

    public function destroy(Campaign $campaign)
    {
        $this->authorize('is-manager');
        $campaign->delete();
        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign berhasil dihapus.');
    }
}
