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
