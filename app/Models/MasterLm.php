<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MasterLm extends Model
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

    protected $casts = [
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'is_delete_pending' => 'boolean',
        'bulan_mulai' => 'integer',
        'bulan_nonaktif' => 'integer',
    ];

    public function scopeActiveInMonth($query, $bulan)
    {
        $bulan = (int)$bulan;
        return $query->where('is_approved', true)
            ->where(function($q) use ($bulan) {
                $q->whereNull('bulan_mulai')
                  ->orWhere('bulan_mulai', '<=', $bulan);
            })
            ->where(function($q) use ($bulan) {
                $q->where('is_active', true)
                  ->orWhere(function($sub) use ($bulan) {
                      $sub->where('is_active', false)
                          ->whereNotNull('bulan_nonaktif')
                          ->where('bulan_nonaktif', '>', $bulan);
                  });
            });
    }

    public function isActiveInMonth($bulan): bool
    {
        $bulan = (int)$bulan;
        if (!$this->is_approved) {
            return false;
        }

        $bulanMulai = $this->bulan_mulai ?? 1;
        if ($bulan < $bulanMulai) {
            return false;
        }

        if (!$this->is_active) {
            if ($this->bulan_nonaktif) {
                if ($bulan >= $this->bulan_nonaktif) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    public function deleteRequestedBy()
    {
        return $this->belongsTo(User::class, 'delete_requested_by');
    }

    public function wig()
    {
        return $this->belongsTo(MasterWig::class, 'wig_id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function sesiWigs()
    {
        return $this->hasMany(SesiWig::class, 'lm_id');
    }

    public function realisasis()
    {
        return $this->hasMany(Realisasi::class, 'lm_id');
    }

    public function breakdowns()
    {
        return $this->hasMany(BreakdownLm::class, 'lm_id');
    }
}
