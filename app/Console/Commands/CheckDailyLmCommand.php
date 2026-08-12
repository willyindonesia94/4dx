<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Realisasi;

class CheckDailyLmCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-daily-lm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check daily LM realization and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();

        // Cari user yang memiliki LM tapi belum mengisi realisasi hari ini
        // Sederhananya, kita asumsikan semua user yang memiliki unit_id perlu diingatkan
        // (atau disesuaikan dengan logic role Anda)
        $users = User::whereNotNull('unit_id')->get();

        $count = 0;
        foreach ($users as $user) {
            $hasFilled = Realisasi::where('user_id', $user->id)
                ->whereDate('tanggal_input', $today)
                ->exists();

            if (!$hasFilled) {
                $user->notify(new \App\Notifications\LmReminderNotification());
                $count++;
            }
        }

        $this->info("Sent daily LM reminder to {$count} users.");
    }
}
