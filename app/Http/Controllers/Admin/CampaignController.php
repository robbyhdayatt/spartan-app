<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

        // **VALIDASI DUPLIKASI CAMPAIGN**
        $this->validateCampaignOverlap(
            $validated['part_id'], 
            $validated['tipe'], 
            $validated['tanggal_mulai'], 
            $validated['tanggal_selesai']
        );

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

        // **VALIDASI DUPLIKASI CAMPAIGN (kecuali campaign yang sedang di-update)**
        $this->validateCampaignOverlap(
            $validated['part_id'], 
            $validated['tipe'], 
            $validated['tanggal_mulai'], 
            $validated['tanggal_selesai'],
            $campaign->id // Exclude campaign yang sedang di-update
        );

        $campaign->update($validated);

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign berhasil diperbarui.');
    }

    public function destroy(Campaign $campaign)
    {
        $this->authorize('is-manager');
        $campaign->delete();
        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign berhasil dihapus.');
    }

    /**
     * Validasi overlap campaign untuk part dan tipe yang sama
     */
    private function validateCampaignOverlap($partId, $tipe, $tanggalMulai, $tanggalSelesai, $excludeId = null)
    {
        $query = Campaign::where('part_id', $partId)
            ->where('tipe', $tipe)
            ->where('is_active', true)
            ->where(function($query) use ($tanggalMulai, $tanggalSelesai) {
                // Cek overlap periode:
                // 1. Campaign baru mulai di tengah campaign existing
                $query->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                    // 2. Campaign baru selesai di tengah campaign existing  
                    ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai])
                    // 3. Campaign existing berada di tengah campaign baru
                    ->orWhere(function($q) use ($tanggalMulai, $tanggalSelesai) {
                        $q->where('tanggal_mulai', '<=', $tanggalMulai)
                          ->where('tanggal_selesai', '>=', $tanggalSelesai);
                    })
                    // 4. Campaign baru menutupi campaign existing
                    ->orWhere(function($q) use ($tanggalMulai, $tanggalSelesai) {
                        $q->where('tanggal_mulai', '>=', $tanggalMulai)
                          ->where('tanggal_selesai', '<=', $tanggalSelesai);
                    });
            });

        // Exclude campaign yang sedang di-update
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingCampaign = $query->with('part')->first();

        if ($existingCampaign) {
            $partName = $existingCampaign->part->nama_part ?? 'Unknown';
            $tipeLabel = $tipe === 'PENJUALAN' ? 'Penjualan' : 'Pembelian';
            
            throw ValidationException::withMessages([
                'part_id' => "Campaign {$tipeLabel} untuk part '{$partName}' sudah ada dalam periode yang overlap (" . 
                           date('d M Y', strtotime($existingCampaign->tanggal_mulai)) . " - " . 
                           date('d M Y', strtotime($existingCampaign->tanggal_selesai)) . "). " .
                           "Silakan pilih periode yang berbeda."
            ]);
        }
    }
}