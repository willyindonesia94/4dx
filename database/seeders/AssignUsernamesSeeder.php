<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AssignUsernamesSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::with('unit')->where('username', null)->orWhere('username', '')->get();
        $count = 0;

        foreach ($users as $user) {
            // Skip Super Admin
            if (in_array(strtolower($user->role_name), ['super admin', 'superadmin'])) {
                continue;
            }

            $username = $this->generateUsername($user);
            
            // Ensure unique username by appending number if exists
            $originalUsername = $username;
            $suffix = 1;
            while (User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $originalUsername . '_' . $suffix;
                $suffix++;
            }

            $user->username = $username;
            $user->password = Hash::make('12345');
            $user->save();
            $count++;
        }

        $this->command->info("Updated {$count} users with generated usernames and default passwords.");
    }

    private function generateUsername($user)
    {
        $role = $user->role_name;
        $matrix = strtoupper($user->matrix_group_id ?? '');
        $unitName = $user->unit ? $user->unit->name : '';
        
        // Clean unit name for suffix (e.g. "UP3 Bandung" -> "BANDUNG", "ULP Bandung Utara" -> "BANDUNG_UTARA")
        $unitSlug = '';
        if (strpos(strtoupper($unitName), 'UP3 ') === 0) {
            $unitSlug = strtoupper(str_replace(' ', '_', substr($unitName, 4)));
        } elseif (strpos(strtoupper($unitName), 'ULP ') === 0) {
            $unitSlug = strtoupper(str_replace(' ', '_', substr($unitName, 4)));
        } else {
            $unitSlug = strtoupper(str_replace(' ', '_', $unitName));
        }

        // UID Roles
        if ($role === 'Perencanaan UID') return 'PERENCANAAN.UID';
        if ($role === 'General Manager UID') return 'GM.UID';
        
        if ($role === 'SRM Bidang UID') {
            if ($matrix === 'NIAGA') return 'SRM.NIAGA';
            if ($matrix === 'JARINGAN') return 'SRM.DISTRIBUSI';
            if ($matrix === 'K3L') return 'SRM.K3L';
            return 'SRM.PERENCANAAN';
        }

        if ($role === 'MSB UID') {
            if ($matrix === 'NIAGA') return 'MSB.STRATSAR';
            if ($matrix === 'JARINGAN') return 'MSB.DALOPHAR';
            if ($matrix === 'TE') return 'MSB.EPM';
            if ($matrix === 'K3L') return 'MSB.K3L';
            return 'MSB.PERENCANAAN';
        }

        if ($role === 'Admin Sub Bidang UID') {
            if ($matrix === 'NIAGA') return 'ADMIN.STRATSAR';
            if ($matrix === 'JARINGAN') return 'ADMIN.DALOPHAR';
            if ($matrix === 'TE') return 'ADMIN.EPM';
            if ($matrix === 'K3L') return 'ADMIN.K3L';
            return 'ADMIN.PERENCANAAN'; // Fallback
        }

        // UP3 Roles
        if ($role === 'Manager UP3') {
            return "MUP3.{$unitSlug}";
        }
        if ($role === 'Asman Perencanaan UP3') {
            return "PERENCANAAN.UP3.{$unitSlug}";
        }
        if ($role === 'Asman Bidang UP3') {
            if ($matrix === 'NIAGA') return "NPS.UP3.{$unitSlug}";
            if ($matrix === 'TE') return "TE.UP3.{$unitSlug}";
            if ($matrix === 'JARINGAN') return "JARINGAN.UP3.{$unitSlug}";
            if ($matrix === 'K3L') return "K3LUP3.{$unitSlug}";
            return "ASMAN.UP3.{$unitSlug}";
        }

        // ULP Roles
        if ($role === 'Manager ULP') {
            return "MULP.{$unitSlug}";
        }
        if ($role === 'Team Leader ULP') {
            if ($matrix === 'NIAGA') return "TL.PP.ULP.{$unitSlug}";
            if ($matrix === 'TE') return "TL.TE.ULP.{$unitSlug}";
            if ($matrix === 'JARINGAN') return "TL.TEKNIK.ULP.{$unitSlug}";
            if ($matrix === 'K3L') return "K3L.ULP.{$unitSlug}";
            return "TL.ULP.{$unitSlug}";
        }
        
        if ($role === 'Staff ULP') {
            return "ADMIN.ULP.{$unitSlug}";
        }

        // UP2D / UP2K
        if ($role === 'UP2D') return "UP2D.{$unitSlug}";
        if ($role === 'UP2K') return "UP2K.{$unitSlug}";

        // Fallback for any other user
        $fallback = str_replace(' ', '.', strtoupper($user->name));
        return $fallback;
    }
}
