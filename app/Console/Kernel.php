<?php

namespace App\Console;

// 1. Tambahkan import untuk command baru kita di bagian atas
use App\Console\Commands\DeactivateExpiredCampaigns;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 2. Daftarkan command kita di sini (opsional tapi praktik yang baik)
        DeactivateExpiredCampaigns::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // 3. Tambahkan jadwal untuk command kita di sini
        $schedule->command('campaigns:deactivate')
                 ->daily()
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
