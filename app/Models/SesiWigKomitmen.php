<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiWigKomitmen extends Model
{

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'hambatans' => 'array',
        'aksi_konkrits' => 'array',
    ];

    public function sesiWig()
    {
        return $this->belongsTo(SesiWig::class);
    }

    public function masterLm()
    {
        return $this->belongsTo(MasterLm::class, 'lm_id');
    }

    public function masterUnit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_id');
    }
}
