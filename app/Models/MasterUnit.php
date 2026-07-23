<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterUnit extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'latitude',
        'longitude',
    ];

    public function parent()
    {
        return $this->belongsTo(MasterUnit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MasterUnit::class, 'parent_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'unit_id');
    }

    public function breakdowns()
    {
        return $this->hasMany(BreakdownLm::class, 'unit_id');
    }

    public function wigBreakdowns()
    {
        return $this->hasMany(BreakdownWig::class, 'unit_id');
    }
}
