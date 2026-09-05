<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BreakdownLm extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $guarded = [];

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_id');
    }

    public function lm()
    {
        return $this->belongsTo(MasterLm::class, 'lm_id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function getBulanIndoAttribute()
    {
        if (!$this->bulan) return '-';
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $months[$this->bulan] ?? '-';
    }

    public function getMingguLabelAttribute()
    {
        $start = \Carbon\Carbon::parse($this->periode_start);
        $end = \Carbon\Carbon::parse($this->periode_end);
        if ($start->diffInDays($end) >= 20) {
            return "Target Total Bulanan";
        }

        static $periodeCache = [];
        $cacheKey = $this->tahun . '_' . $this->bulan;

        if (!array_key_exists($cacheKey, $periodeCache)) {
            $periodeCache[$cacheKey] = MasterPeriode::where('tahun', $this->tahun)->where('bulan', $this->bulan)->first();
        }

        $master = $periodeCache[$cacheKey];
        if ($master) {
            $s = $this->periode_start;
            if ($master->start_m1 == $s) return "Minggu 1";
            if ($master->start_m2 == $s) return "Minggu 2";
            if ($master->start_m3 == $s) return "Minggu 3";
            if ($master->start_m4 == $s) return "Minggu 4";
            if ($master->start_m5 == $s) return "Minggu 5";
        }
        
        return "Target Mingguan";
    }
}
