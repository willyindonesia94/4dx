<?php

namespace App\Imports;

use App\Models\MasterUnit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MasterUnitImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $name = trim($row['nama_unit'] ?? '');
        $type = strtoupper(trim($row['tipe_uidup3up2dup2kulp'] ?? ''));
        
        if (empty($name) || empty($type)) {
            return null; // Skip invalid rows
        }

        $parentName = trim($row['nama_induk_unit'] ?? '');
        $parentId = null;

        if (!empty($parentName)) {
            $parentUnit = MasterUnit::where('name', $parentName)->first();
            if ($parentUnit) {
                $parentId = $parentUnit->id;
            }
        }

        $latitude = $row['latitude'] ?? null;
        $longitude = $row['longitude'] ?? null;

        return MasterUnit::updateOrCreate(
            [
                'name' => $name,
                'type' => $type
            ],
            [
                'parent_id' => $parentId,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]
        );
    }
}
