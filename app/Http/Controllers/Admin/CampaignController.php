<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Part;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with(['parts', 'suppliers'])->latest()->get();
        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $this->authorize('is-manager');
        $parts = Part::where('is_active', true)->orderBy('nama_part')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();
        return view('admin.campaigns.create', compact('parts', 'suppliers'));
    }

    public function store(Request $request)
    {
        $this->authorize('is-manager');
        $request->validate([
            'nama_campaign' => 'required|string|max:255|unique:campaigns',
            'tipe' => 'required|in:PENJUALAN,PEMBELIAN',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'part_ids' => 'nullable|array',
            'part_ids.*' => 'exists:parts,id',
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        DB::beginTransaction();
        try {
            $campaign = Campaign::create($request->except(['part_ids', 'supplier_ids']) + [
                'created_by' => Auth::id()
            ]);

            $campaign->parts()->sync($request->part_ids ?? []);
            $campaign->suppliers()->sync($request->supplier_ids ?? []);

            DB::commit();
            return redirect()->route('admin.campaigns.index')->with('success', 'Campaign berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Campaign $campaign)
    {
        $this->authorize('is-manager');
        $parts = Part::where('is_active', true)->orderBy('nama_part')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('nama_supplier')->get();
        return view('admin.campaigns.edit', compact('campaign', 'parts', 'suppliers'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $this->authorize('is-manager');
        $request->validate([
            'nama_campaign' => 'required|string|max:255|unique:campaigns,nama_campaign,' . $campaign->id,
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'part_ids' => 'nullable|array',
            'part_ids.*' => 'exists:parts,id',
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        DB::beginTransaction();
        try {
            $campaign->update($request->except(['part_ids', 'supplier_ids']));

            $campaign->parts()->sync($request->part_ids ?? []);
            $campaign->suppliers()->sync($request->supplier_ids ?? []);

            DB::commit();
            return redirect()->route('admin.campaigns.index')->with('success', 'Campaign berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Campaign $campaign)
    {
        $this->authorize('is-manager');
        $campaign->delete();
        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign berhasil dihapus.');
    }
}