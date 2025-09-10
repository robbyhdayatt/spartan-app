<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesTarget;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Penjualan;
use App\Models\Incentive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncentiveController extends Controller
{
    // Halaman untuk menampilkan dan menetapkan target
    public function targets(Request $request)
    {
        $this->authorize('is-manager');

        $jabatanSalesId = Jabatan::where('nama_jabatan', 'Sales')->firstOrFail()->id;
        $salesUsers = User::where('jabatan_id', $jabatanSalesId)->where('is_active', true)->get();

        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan', now()->month);

        // Ambil target yang sudah ada untuk ditampilkan
        $existingTargets = SalesTarget::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->pluck('target_amount', 'user_id');

        return view('admin.incentives.targets', compact('salesUsers', 'tahun', 'bulan', 'existingTargets'));
    }

    // Menyimpan data target baru
    public function storeTarget(Request $request)
    {
        $this->authorize('is-manager');

        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
            'targets' => 'required|array',
            'targets.*' => 'required|numeric|min:0',
        ]);

        foreach ($request->targets as $userId => $targetAmount) {
            SalesTarget::updateOrCreate(
                [
                    'user_id' => $userId,
                    'tahun' => $request->tahun,
                    'bulan' => $request->bulan,
                ],
                [
                    'target_amount' => $targetAmount,
                    'created_by' => Auth::id(),
                ]
            );
        }

        return back()->with('success', 'Target penjualan berhasil disimpan.');
    }

    // Halaman untuk menampilkan laporan insentif
    public function report(Request $request)
    {
        $this->authorize('is-manager');

        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan', now()->month);
        $periode = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT);

        // 1. Hitung ulang dan simpan data untuk memastikan datanya up-to-date
        $targets = SalesTarget::with('user')
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->get();

        foreach ($targets as $target) {
            $totalPenjualan = Penjualan::where('sales_id', $target->user_id)
                ->whereYear('tanggal_jual', $tahun)
                ->whereMonth('tanggal_jual', $bulan)
                ->sum('subtotal');

            $pencapaian = ($target->target_amount > 0) ? ($totalPenjualan / $target->target_amount) * 100 : 0;
            $jumlahInsentif = 0;

            if ($pencapaian >= 100) {
                $jumlahInsentif = $totalPenjualan * 0.02; // 2%
            } elseif ($pencapaian >= 80) {
                $jumlahInsentif = $totalPenjualan * 0.01; // 1%
            }

            Incentive::updateOrCreate(
                ['sales_target_id' => $target->id, 'periode' => $periode],
                [
                    'user_id' => $target->user_id,
                    'total_penjualan' => $totalPenjualan,
                    'persentase_pencapaian' => $pencapaian,
                    'jumlah_insentif' => $jumlahInsentif,
                ]
            );
        }

        // 2. Ambil data yang sudah final dari tabel incentives
        $reportData = Incentive::with(['user', 'salesTarget'])
            ->where('periode', $periode)
            ->get();

        return view('admin.incentives.report', compact('reportData', 'tahun', 'bulan'));
    }

    // --- METHOD BARU UNTUK MENANDAI PEMBAYARAN ---
    public function markAsPaid(Incentive $incentive)
    {
        $this->authorize('is-manager');

        if ($incentive->status === 'UNPAID') {
            $incentive->status = 'PAID';
            $incentive->paid_at = now();
            $incentive->save();
            return back()->with('success', 'Insentif untuk ' . $incentive->user->nama . ' telah ditandai sebagai LUNAS.');
        }

        return back()->with('error', 'Status insentif ini sudah lunas.');
    }
}
