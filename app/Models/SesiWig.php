<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiWig extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_sesi',
        'tahun',
        'bulan',
        'minggu_ke',
        'tipe_sesi',
        'tanggal_pelaksanaan',
        'level_terlibat',
        'komitmen',
        'evaluasi',
    ];

    protected $casts = [
        'level_terlibat' => 'array',
        'tanggal_pelaksanaan' => 'date',
    ];

    public function presenters()
    {
        return $this->belongsToMany(MasterUnit::class, 'sesi_wig_presenters', 'sesi_wig_id', 'unit_id')->withTimestamps();
    }
}
