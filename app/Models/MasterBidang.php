<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MasterBidang extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'name',
        'level',
        'parent_id',
        'description',
    ];

    public function parent()
    {
        return $this->belongsTo(MasterBidang::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MasterBidang::class, 'parent_id');
    }

    public static function getRelatedDivisions($matrixGroup)
    {
        if (empty($matrixGroup) || strtoupper(trim($matrixGroup)) === 'ALL') {
            return [];
        }
        
        $target = trim($matrixGroup);
        $results = [$target];
        
        $node = self::where('name', $target)->first();
        if (!$node) return $results;

        // Dapatkan semua ancestor (induk), tapi kecualikan UID_BIDANG sesuai request
        $curr = $node->parent;
        while ($curr) {
            if ($curr->level !== 'UID_BIDANG') {
                $results[] = $curr->name;
            }
            $curr = $curr->parent;
        }

        // Dapatkan semua descendant (anak, cucu, dst) secara rekursif
        $getDescendants = function($parent) use (&$getDescendants, &$results) {
            $children = self::where('parent_id', $parent->id)->get();
            foreach ($children as $child) {
                if (!in_array($child->name, $results)) {
                    $results[] = $child->name;
                }
                $getDescendants($child);
            }
        };
        $getDescendants($node);

        return array_values(array_unique($results));
    }
}
