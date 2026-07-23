<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $uid = \App\Models\Location::firstOrCreate([
            'name' => 'PLN UID Jawa Barat',
            'type' => 'UID',
            'parent_id' => null
        ]);

        $up3Bandung = \App\Models\Location::firstOrCreate([
            'name' => 'UP3 Bandung',
            'type' => 'UP3',
            'parent_id' => $uid->id
        ]);
        
        $up3Bogor = \App\Models\Location::firstOrCreate([
            'name' => 'UP3 Bogor',
            'type' => 'UP3',
            'parent_id' => $uid->id
        ]);

        $up3Cirebon = \App\Models\Location::firstOrCreate([
            'name' => 'UP3 Cirebon',
            'type' => 'UP3',
            'parent_id' => $uid->id
        ]);

        $up3Tasikmalaya = \App\Models\Location::firstOrCreate([
            'name' => 'UP3 Tasikmalaya',
            'type' => 'UP3',
            'parent_id' => $uid->id
        ]);

        // ULPs for UP3 Bandung
        $ulpsBandung = ['Bandung Utara', 'Bandung Selatan', 'Bandung Barat', 'Bandung Timur', 'Cimahi Selatan'];
        foreach ($ulpsBandung as $ulp) {
            \App\Models\Location::firstOrCreate([
                'name' => 'ULP ' . $ulp,
                'type' => 'ULP',
                'parent_id' => $up3Bandung->id
            ]);
        }
    }
}
