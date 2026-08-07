<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MasterPeriode extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Mengambil tanggal mingguan (M1-M5) untuk suatu bulan.
     * Mengacu pada tabel database jika ada, jika belum di-generate maka fallback ke rumus otomatis (Senin Pertama).
     */
    public static function getWeekDates($tahun, $bulan)
    {
        $periode = self::where('tahun', $tahun)->where('bulan', $bulan)->first();
        
        if ($periode) {
            return [
                'target_m1' => $periode->start_m1 && $periode->end_m1 ? ['start' => $periode->start_m1, 'end' => $periode->end_m1] : null,
                'target_m2' => $periode->start_m2 && $periode->end_m2 ? ['start' => $periode->start_m2, 'end' => $periode->end_m2] : null,
                'target_m3' => $periode->start_m3 && $periode->end_m3 ? ['start' => $periode->start_m3, 'end' => $periode->end_m3] : null,
                'target_m4' => $periode->start_m4 && $periode->end_m4 ? ['start' => $periode->start_m4, 'end' => $periode->end_m4] : null,
                'target_m5' => $periode->start_m5 && $periode->end_m5 ? ['start' => $periode->start_m5, 'end' => $periode->end_m5] : null,
            ];
        }

        return self::calculateDefaultWeeks($tahun, $bulan);
    }

    /**
     * Rumus dinamis menghitung minggu berantai setiap 7 hari, 
     * di mana Minggu 1 (M1) dipastikan selalu berawal dari Hari Senin pertama di bulan tersebut.
     */
    public static function calculateDefaultWeeks($tahun, $bulan)
    {
        // 1. Tentukan awal tahun dan hari Senin pertama untuk tahun ini
        $firstDayOfYear = Carbon::create($tahun, 1, 1);
        $startOfWeek = $firstDayOfYear->copy();
        
        // Jika 1 Januari bukan Senin, mundur ke Senin sebelumnya
        if (!$startOfWeek->isMonday()) {
            $startOfWeek->previous(Carbon::MONDAY);
        }

        // 2. Generate seluruh minggu dalam setahun penuh (continuous 7 days)
        $allWeeks = [];
        $currentStart = $startOfWeek->copy();
        
        // Loop kira-kira 53 minggu maksimal untuk satu tahun
        while (count($allWeeks) < 53) {
            $currentEnd = $currentStart->copy()->addDays(6);
            $allWeeks[] = [
                'start' => $currentStart->copy(),
                'end' => $currentEnd->copy()
            ];
            $currentStart = $currentEnd->copy()->addDay();
            
            // Berhenti jika minggu ini mulai di tahun BERIKUTNYA
            if ($currentStart->year > $tahun) {
                break;
            }
        }

        // 3. Filter minggu untuk bulan yang diminta
        $monthWeeks = [];
        foreach ($allWeeks as $index => $w) {
            $wStart = $w['start'];
            
            // Sebuah minggu masuk ke $bulan jika:
            // a) start date-nya ada di bulan tersebut
            // b) ATAU ini adalah minggu PERTAMA dari array (index 0) dan kita sedang mencari bulan Januari (1), meskipun start date-nya di Desember tahun lalu
            if ($wStart->month == $bulan || ($index === 0 && $bulan == 1 && $wStart->month == 12)) {
                $monthWeeks[] = $w;
            }
        }

        // 4. Format output sesuai ekspektasi (M1-M5)
        $weeks = [];
        for ($i = 1; $i <= 5; $i++) {
            if (isset($monthWeeks[$i - 1])) {
                $weeks["target_m{$i}"] = [
                    'start' => $monthWeeks[$i - 1]['start']->format('Y-m-d'),
                    'end' => $monthWeeks[$i - 1]['end']->format('Y-m-d')
                ];
            } else {
                $weeks["target_m{$i}"] = null;
            }
        }

        return $weeks;
    }
}
