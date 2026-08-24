<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UpdateUP3UsersFormatSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users who have UP3 roles
        $users = User::with('unit')->where('role_name', 'LIKE', '%UP3%')->get();
        $count = 0;

        foreach ($users as $user) {
            $unitName = $user->unit ? strtoupper($user->unit->name) : '';
            if (empty($unitName)) {
                continue;
            }

            // Extract the slug (e.g., "UP3 BANDUNG" -> "BANDUNG")
            $unitSlug = '';
            if (strpos($unitName, 'UP3 ') === 0) {
                $unitSlug = str_replace(' ', '_', substr($unitName, 4));
            } else {
                $unitSlug = str_replace(' ', '_', $unitName);
            }

            $role = $user->role_name;
            $matrix = strtoupper($user->matrix_group_id ?? '');

            $newName = '';
            $newUsername = '';

            if ($role === 'Manager UP3') {
                $newName = "MANAGER {$unitName}";
                $newUsername = "MUP3.{$unitSlug}";
            } elseif ($role === 'Asman Perencanaan UP3') {
                $newName = "ASMAN PERENCANAAN {$unitName}";
                $newUsername = "PERENCANAAN.UP3.{$unitSlug}";
            } elseif ($role === 'Asman Bidang UP3') {
                if ($matrix === 'NIAGA') {
                    $newName = "ASMAN NPS {$unitName}";
                    $newUsername = "NPS.UP3.{$unitSlug}";
                } elseif ($matrix === 'TE') {
                    $newName = "ASMAN TE {$unitName}";
                    $newUsername = "TE.UP3.{$unitSlug}";
                } elseif ($matrix === 'JARINGAN') {
                    $newName = "ASMAN JARINGAN {$unitName}";
                    $newUsername = "JARINGAN.UP3.{$unitSlug}";
                } elseif ($matrix === 'K3L') {
                    $newName = "K3L {$unitName}";
                    $newUsername = "K3L.UP3.{$unitSlug}";
                } else {
                    $newName = "ASMAN {$unitName}";
                    $newUsername = "ASMAN.UP3.{$unitSlug}";
                }
            }

            if (!empty($newName) && !empty($newUsername)) {
                // Check if username already exists for another user to avoid unique constraint violation
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

        $this->command->info("Successfully updated {$count} UP3 users format.");
    }
}
