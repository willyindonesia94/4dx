<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiWig extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function wig()
    {
        return $this->belongsTo(MasterWig::class, 'wig_id');
    }

    public function unit()
    {
        return $this->belongsTo(MasterUnit::class, 'unit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
