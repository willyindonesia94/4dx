<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProyeksiWig extends Model
{
    use HasFactory;

    protected $table = 'proyeksi_wigs';

    protected $fillable = [
        'wig_id',
        'angka_proyeksi',
        'capaian_persen',
        'catatan',
        'user_id',
    ];

    public function wig()
    {
        return $this->belongsTo(MasterWig::class, 'wig_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
