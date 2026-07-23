<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Target;
use App\Models\Realization;
use Carbon\Carbon;

class GenerateDummyRealizationsSeeder extends Seeder
{
    public function run(): void
    {
        $leadMeasures = Target::where('type', 'Lead Measure')->get();
        
        foreach ($leadMeasures as $lm) {
            // Delete old just in case
            Realization::where('target_id', $lm->id)->delete();
            
            // Create 3 days of realizations
            $baseValue = $lm->target_value / 4; // reasonable chunk
            
            Realization::create([
                'target_id' => $lm->id,
                'report_date' => Carbon::now()->subDays(2),
                'realization_value' => round($baseValue * 0.8, 1),
                'created_by' => $lm->created_by
            ]);
            
            Realization::create([
                'target_id' => $lm->id,
                'report_date' => Carbon::now()->subDays(1),
                'realization_value' => round($baseValue * 1.1, 1),
                'created_by' => $lm->created_by
            ]);
            
            Realization::create([
                'target_id' => $lm->id,
                'report_date' => Carbon::now(),
                'realization_value' => round($baseValue, 1),
                'created_by' => $lm->created_by
            ]);
        }
    }
}
