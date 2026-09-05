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

        // MAPPING PENYEDERHANAAN
        $mapping = [
            'NIAGA' => 'Strategi Pemasaran (MSB)',
            'JARINGAN' => 'Pengendalian Operasi dan Pemeliharaan (MSB)',
            'TE' => 'EPM (MSB)',
            'K3L' => 'K3L',
        ];

        if (isset($mapping[strtoupper($target)])) {
            $mappedName = $mapping[strtoupper($target)];
            $results[] = $mappedName;
            $node = self::where('name', $mappedName)->first();
        } else {
            $node = self::where('name', $target)->first();
        }
        if (!$node) return $results;

        // Dapatkan semua ancestor (induk). Batasan UID_BIDANG dihapus agar SRM bisa melihat WIG bidangnya sendiri.
        $curr = $node->parent;
        while ($curr) {
            $results[] = $curr->name;
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
