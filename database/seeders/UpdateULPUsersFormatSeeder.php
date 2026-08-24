<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UpdateULPUsersFormatSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::with('unit')->where('role_name', 'LIKE', '%ULP%')->get();
        $count = 0;

        foreach ($users as $user) {
            $unitName = $user->unit ? strtoupper($user->unit->name) : '';
            if (empty($unitName)) continue;

            // Clean unit name (remove "ULP ")
            $unitNameClean = '';
            if (strpos($unitName, 'ULP ') === 0) {
                $unitNameClean = substr($unitName, 4);
            } else {
                $unitNameClean = $unitName;
            }

            // Slug for username: BANDUNG UTARA -> BANDUNG.UTARA
            $unitSlug = str_replace(' ', '.', $unitNameClean);
            
            $role = $user->role_name;
            $matrix = strtoupper($user->matrix_group_id ?? '');

            $newName = '';
            $newUsername = '';

            if ($role === 'Manager ULP') {
                $newName = "MULP {$unitNameClean}";
                $newUsername = "MULP.{$unitSlug}";
            } elseif ($role === 'Team Leader ULP' || $role === 'Staff ULP') {
                if ($matrix === 'NIAGA') {
                    $newName = "TL RETAIL/PP ULP {$unitNameClean}";
                    $newUsername = "TL.PP.{$unitSlug}";
                } elseif ($matrix === 'TE') {
                    $newName = "TL TE ULP {$unitNameClean}";
                    $newUsername = "TL.TE.{$unitSlug}";
                } elseif ($matrix === 'JARINGAN') {
                    $newName = "TL TEKNIK ULP {$unitNameClean}";
                    $newUsername = "TL.TEKNIK.{$unitSlug}";
                } elseif ($matrix === 'K3L') {
                    $newName = "K3L ULP {$unitNameClean}";
                    $newUsername = "K3L.{$unitSlug}";
                } else {
                    $newName = "ADMIN ULP {$unitNameClean}";
                    $newUsername = "ADMIN.ULP.{$unitSlug}";
                }
            }

            if (!empty($newName) && !empty($newUsername)) {
                $originalUsername = $newUsername;
                $suffix = 1;
                while (User::where('username', $newUsername)->where('id', '!=', $user->id)->exists()) {
                    $newUsername = $originalUsername . '_' . $suffix;
                    $suffix++;
                }

                $user->name = $newName;
                $user->username = $newUsername;
                $user->password = Hash::make('12345');
                $user->save();
                
                $count++;
            }
        }

        $this->command->info("Successfully updated {$count} ULP users format.");
    }
}
