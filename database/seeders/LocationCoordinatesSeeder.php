<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationCoordinatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coordinates = [
            'ULP Bandung Utara' => ['lat' => -6.8833, 'lng' => 107.6167],
            'ULP Bandung Selatan' => ['lat' => -6.9500, 'lng' => 107.6167],
            'ULP Bandung Timur' => ['lat' => -6.9333, 'lng' => 107.6833],
            'ULP Bandung Barat' => ['lat' => -6.9000, 'lng' => 107.5500],
            'ULP Cimahi Selatan' => ['lat' => -6.9011, 'lng' => 107.5451],
            
            // Bogor Area
            'ULP Bogor Kota' => ['lat' => -6.5950, 'lng' => 106.8060],
            'ULP Cibinong' => ['lat' => -6.4820, 'lng' => 106.8520],
            'ULP Depok Kota' => ['lat' => -6.4020, 'lng' => 106.8180],
            
            // Cirebon Area
            'ULP Cirebon Kota' => ['lat' => -6.7050, 'lng' => 108.5550],
            'ULP Sumber' => ['lat' => -6.7580, 'lng' => 108.4790],
            'ULP Jatibarang' => ['lat' => -6.4740, 'lng' => 108.3150],
            
            // Tasikmalaya Area
            'ULP Tasikmalaya Kota' => ['lat' => -7.3270, 'lng' => 108.2200],
            'ULP Singaparna' => ['lat' => -7.3510, 'lng' => 108.1110],
            'ULP Banjar' => ['lat' => -7.3680, 'lng' => 108.5350],
        ];

        foreach ($coordinates as $name => $coord) {
            \App\Models\Location::where('name', $name)->update([
                'latitude' => $coord['lat'],
                'longitude' => $coord['lng']
            ]);
        }
    }
}
