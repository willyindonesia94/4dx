<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiWigKomitmen extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
