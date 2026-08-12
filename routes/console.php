<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:check-daily-lm')->timezone('Asia/Jakarta')->dailyAt('08:00');
Schedule::command('app:check-daily-lm')->timezone('Asia/Jakarta')->dailyAt('12:00');
Schedule::command('app:check-daily-lm')->timezone('Asia/Jakarta')->dailyAt('16:00');

// WIG bulanan dicek di hari terakhir pada jam tertentu
Schedule::command('app:check-monthly-wig')->timezone('Asia/Jakarta')->dailyAt('08:00')->when(fn () => now()->timezone('Asia/Jakarta')->isLastOfMonth());
Schedule::command('app:check-monthly-wig')->timezone('Asia/Jakarta')->dailyAt('12:00')->when(fn () => now()->timezone('Asia/Jakarta')->isLastOfMonth());
Schedule::command('app:check-monthly-wig')->timezone('Asia/Jakarta')->dailyAt('16:00')->when(fn () => now()->timezone('Asia/Jakarta')->isLastOfMonth());
