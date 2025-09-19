<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredCampaigns extends Command
{
    /**
     * Nama dan signature dari console command.
     * Inilah yang akan kita ketik di terminal: php artisan campaigns:deactivate
     */
    protected $signature = 'campaigns:deactivate';

    /**
     * Deskripsi dari console command.
     */
    protected $description = 'Mencari dan menonaktifkan campaign yang sudah kedaluwarsa';

    /**
     * Buat sebuah instance command baru.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Eksekusi console command.
     */
    public function handle()
    {
        $this->info('Memulai proses penonaktifan campaign kedaluwarsa...');
        Log::info('Scheduler: Memulai pengecekan campaign kedaluwarsa.');

        $today = Carbon::today();

        // Cari semua campaign yang:
        // 1. Masih berstatus aktif (is_active = true)
        // 2. Tanggal selesainya sudah lewat dari kemarin (tanggal_selesai < hari ini)
        $expiredCampaigns = Campaign::where('is_active', true)
            ->where('tanggal_selesai', '<', $today)
            ->get();

        if ($expiredCampaigns->isEmpty()) {
            $this->info('Tidak ada campaign kedaluwarsa yang ditemukan.');
            Log::info('Scheduler: Tidak ada campaign kedaluwarsa untuk dinonaktifkan.');
            return 0; // 0 menandakan command sukses
        }

        $count = $expiredCampaigns->count();
        $this->info("Ditemukan {$count} campaign yang akan dinonaktifkan.");

        foreach ($expiredCampaigns as $campaign) {
            $campaign->is_active = false;
            $campaign->save();
            $this->line("Campaign '{$campaign->nama_campaign}' (ID: {$campaign->id}) telah dinonaktifkan.");
        }

        Log::info("Scheduler: Berhasil menonaktifkan {$count} campaign.");
        $this->info('Proses selesai.');

        return 0;
    }
}
