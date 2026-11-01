<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MasterWig extends Model
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

    protected $hidden = ['breakdowns'];

    protected $casts = [
        'divisi' => 'array',
    ];

    protected $fillable = [
        'judul',
        'deskripsi',
        'divisi',
        'unit_pemilik_id',
        'angka_target',
        'satuan_id',
        'polaritas',
        'is_approved',
        'is_delete_pending',
        'delete_requested_by',
        'delete_reason',
    ];

    public function deleteRequestedBy()
    {
        return $this->belongsTo(User::class, 'delete_requested_by');
    }

    public function unitPemilik()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_pemilik_id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function masterLms()
    {
        return $this->hasMany(MasterLm::class, 'wig_id');
    }

    public function breakdowns()
    {
        return $this->hasMany(BreakdownWig::class, 'wig_id');
    }

    public function realisasis()
    {
        return $this->hasMany(RealisasiWig::class, 'wig_id');
    }

    public function proyeksis()
    {
        return $this->hasMany(ProyeksiWig::class, 'wig_id');
    }

    public function latestProyeksi()
    {
        return $this->hasOne(ProyeksiWig::class, 'wig_id')->latestOfMany();
    }
}
