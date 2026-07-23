<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterWig extends Model
{
    use HasFactory;
    protected $fillable = [
        'judul',
        'deskripsi',
        'divisi',
        'unit_pemilik_id',
        'angka_target',
        'satuan_id',
        'polaritas',
        'is_approved',
    ];

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
}
