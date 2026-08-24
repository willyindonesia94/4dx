<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class SyncNewRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Rename existing roles in Spatie
        $roleRenames = [
            'Bidang UID' => 'SRM Bidang UID',
            'Sub Bidang UID' => 'MSB UID',
            'Perencanaan UP3' => 'Asman Perencanaan UP3',
            'Bidang UP3' => 'Asman Bidang UP3',
        ];

        foreach ($roleRenames as $old => $new) {
            $role = Role::where('name', $old)->first();
            if ($role) {
                $role->update(['name' => $new]);
            }
        }

        // 2. Create new roles
        $newRoles = [
            'SRM Perencanaan UID',
            'Admin Sub Bidang UID',
        ];

        foreach ($newRoles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // 3. Update user's role_name column (to keep it synced with Spatie)
        foreach ($roleRenames as $old => $new) {
            User::where('role_name', $old)->update(['role_name' => $new]);
        }

        // 4. Clean up matrix_group_id
        // Matrix groups standard: ALL, NIAGA, JARINGAN, TE, K3L
        $users = User::all();
        foreach ($users as $user) {
            $current = $user->matrix_group_id;
            
            // Skip if already null
            if ($current === null || $current === '') {
                $user->matrix_group_id = null;
                $user->save();
                continue;
            }
            
            $upper = strtoupper(trim((string)$current));
            
            if ($upper === 'ALL' || str_contains($upper, 'PERENCANAAN')) {
                $user->matrix_group_id = 'ALL';
            } elseif (str_contains($upper, 'NIAGA') || str_contains($upper, 'PELAYANAN') || str_contains($upper, 'PEMASARAN') || str_contains($upper, 'NPS') || str_contains($upper, 'RETAIL')) {
                $user->matrix_group_id = 'NIAGA';
            } elseif (str_contains($upper, 'JARINGAN') || str_contains($upper, 'DISTRIBUSI') || str_contains($upper, 'DALOPHAR') || str_contains($upper, 'OPERASI') || str_contains($upper, 'TEKNIK')) {
                $user->matrix_group_id = 'JARINGAN';
            } elseif (str_contains($upper, 'TE') || str_contains($upper, 'TRANSAKSI') || str_contains($upper, 'EPM')) {
                $user->matrix_group_id = 'TE';
            } elseif (str_contains($upper, 'K3L') || str_contains($upper, 'KESELAMATAN') || str_contains($upper, 'FRA')) {
                $user->matrix_group_id = 'K3L';
            } elseif (str_contains($upper, 'UP2D') || str_contains($upper, 'UP2K')) {
                // Keep original value for UP2D and UP2K based on agreement
                $user->matrix_group_id = $current;
            } else {
                // Weird values like "14" will be nulled
                $user->matrix_group_id = null;
            }

            if ($user->isDirty('matrix_group_id')) {
                $user->save();
            }
        }
    }
}
