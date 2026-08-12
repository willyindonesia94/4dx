<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckMonthlyWigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-monthly-wig';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check monthly WIG realization and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Sama seperti LM, kita asumsikan semua user dengan unit_id harus di cek
        $users = User::whereNotNull('unit_id')->get();

        $count = 0;
        foreach ($users as $user) {
            // Asumsi table realisasi_wigs mempunyai relasi atau tanggal untuk pengecekan bulan ini.
            // Karena tidak ada struktur pasti saat ini, kita cek berdasarkan bulan berjalan di created_at atau field relevan.
            $hasFilled = \App\Models\RealisasiWig::where('user_id', $user->id)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->exists();

            if (!$hasFilled) {
                $user->notify(new \App\Notifications\WigReminderNotification());
                $count++;
            }
        }

        $this->info("Sent monthly WIG reminder to {$count} users.");
    }
}
