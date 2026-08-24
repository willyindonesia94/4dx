<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MasterUnit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportSpreadsheetULPSeeder extends Seeder
{
    public function run(): void
    {
        $jsonFile = base_path('ulp_list.json');
        if (!file_exists($jsonFile)) {
            $this->command->error("ulp_list.json not found!");
            return;
        }

        $data = json_decode(file_get_contents($jsonFile), true);
        $currentUp3Id = null;
        $usersCreated = 0;
        $unitsCreated = 0;

        $processedUlps = [];

        foreach ($data as $row) {
            if (empty($row[0])) continue;
            
            $cellValue = trim($row[0]);
            
            if (strpos(strtoupper($cellValue), 'UP3 ') === 0) {
                // It's a UP3
                $up3 = MasterUnit::whereRaw('LOWER(name) = ?', [strtolower($cellValue)])
                    ->where('type', 'UP3')
                    ->first();
                    
                if (!$up3) {
                    $up3 = MasterUnit::create([
                        'name' => mb_convert_case($cellValue, MB_CASE_TITLE, "UTF-8"),
                        'type' => 'UP3',
                        'parent_id' => 1 // UID Jawa Barat
                    ]);
                    $unitsCreated++;
                }
                $currentUp3Id = $up3->id;
            } 
            elseif (strpos(strtoupper($cellValue), 'ULP ') === 0) {
                // It's a ULP
                if (in_array(strtoupper($cellValue), $processedUlps)) {
                    continue; // Skip duplicates if spreadsheet repeats
                }
                $processedUlps[] = strtoupper($cellValue);

                $ulpName = $cellValue;
                $ulpNameClean = substr($ulpName, 4);

                $ulp = MasterUnit::whereRaw('LOWER(name) = ?', [strtolower($ulpName)])
                    ->where('type', 'ULP')
                    ->first();
                    
                if (!$ulp) {
                    $ulp = MasterUnit::create([
                        'name' => mb_convert_case($ulpName, MB_CASE_TITLE, "UTF-8"),
                        'type' => 'ULP',
                        'parent_id' => $currentUp3Id
                    ]);
                    $unitsCreated++;
                } else {
                    // Update parent_id if it was null
                    if (!$ulp->parent_id && $currentUp3Id) {
                        $ulp->parent_id = $currentUp3Id;
                        $ulp->save();
                    }
                }

                // Slug for username: BANDUNG UTARA -> BANDUNG.UTARA
                $unitSlug = str_replace(' ', '.', strtoupper($ulpNameClean));
                $unitSlug = str_replace('-', '.', $unitSlug); // Just in case
                
                $rolesToCreate = [
                    [
                        'role' => 'Manager ULP',
                        'matrix' => 'ALL',
                        'name' => "MULP " . strtoupper($ulpNameClean),
                        'username' => "MULP.{$unitSlug}",
                    ],
                    [
                        'role' => 'Team Leader ULP',
                        'matrix' => 'NIAGA',
                        'name' => "TL RETAIL/PP ULP " . strtoupper($ulpNameClean),
                        'username' => "TL.PP.{$unitSlug}",
                    ],
                    [
                        'role' => 'Team Leader ULP',
                        'matrix' => 'TE',
                        'name' => "TL TE ULP " . strtoupper($ulpNameClean),
                        'username' => "TL.TE.{$unitSlug}",
                    ],
                    [
                        'role' => 'Team Leader ULP',
                        'matrix' => 'JARINGAN',
                        'name' => "TL TEKNIK ULP " . strtoupper($ulpNameClean),
                        'username' => "TL.TEKNIK.{$unitSlug}",
                    ],
                    [
                        'role' => 'Team Leader ULP',
                        'matrix' => 'K3L',
                        'name' => "K3L ULP " . strtoupper($ulpNameClean),
                        'username' => "K3L.{$unitSlug}",
                    ]
                ];

                foreach ($rolesToCreate as $account) {
                    // Avoid duplicating usernames if they already exist with a different format
                    $user = User::where('unit_id', $ulp->id)
                        ->where('role_name', $account['role'])
                        ->where('matrix_group_id', $account['matrix'])
                        ->first();

                    if (!$user) {
                        // Check if username is taken by someone else
                        $username = $account['username'];
                        $suffix = 1;
                        while(User::where('username', $username)->exists()) {
                            $username = $account['username'] . '_' . $suffix;
                            $suffix++;
                        }

                        User::create([
                            'name' => $account['name'],
                            'username' => $username,
                            'password' => Hash::make('12345'),
                            'role_name' => $account['role'],
                            'unit_id' => $ulp->id,
                            'matrix_group_id' => $account['matrix']
                        ]);
                        $usersCreated++;
                    } else {
                        // Update existing
                        $user->name = $account['name'];
                        // Only update username if not taken by another ID
                        if (!User::where('username', $account['username'])->where('id', '!=', $user->id)->exists()) {
                            $user->username = $account['username'];
                        }
                        $user->password = Hash::make('12345');
                        $user->save();
                    }
                }
            }
        }

        $this->command->info("Created/Updated {$unitsCreated} Units and {$usersCreated} ULP Users based on spreadsheet.");
    }
}
