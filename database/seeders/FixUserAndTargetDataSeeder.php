<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Location;
use App\Models\Target;
use App\Models\Realization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FixUserAndTargetDataSeeder extends Seeder
{
    public function run(): void
    {
        $roleUp3 = Role::where('name', 'admin_up3')->first();
        $roleUlp = Role::where('name', 'admin_ulp')->first();

        $up3s = Location::where('type', 'UP3')->get();
        $ulps = Location::where('type', 'ULP')->get();

        // 1. Create Users for all UP3s
        foreach ($up3s as $up3) {
            $email = 'admin.' . Str::slug(str_replace('UP3 ', '', $up3->name)) . '@pln.co.id';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Manager ' . $up3->name,
                    'password' => Hash::make('password'),
                    'location_id' => $up3->id,
                ]
            );
            if (!$user->roles()->where('role_id', $roleUp3->id)->exists()) {
                $user->roles()->attach($roleUp3->id);
            }
        }

        // 2. Create Users for all ULPs
        $ulpUsers = [];
        foreach ($ulps as $ulp) {
            $email = 'admin.' . Str::slug(str_replace('ULP ', '', $ulp->name)) . '@pln.co.id';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Admin ' . $ulp->name,
                    'password' => Hash::make('password'),
                    'location_id' => $ulp->id,
                ]
            );
            if (!$user->roles()->where('role_id', $roleUlp->id)->exists()) {
                $user->roles()->attach($roleUlp->id);
            }
            $ulpUsers[$ulp->id] = $user->id;
        }

        // 3. Reassign Lead Measures (targets) to their respective ULP Users
        $leadMeasures = Target::where('type', 'Lead Measure')->get();
        foreach ($leadMeasures as $lm) {
            if (isset($ulpUsers[$lm->location_id])) {
                $lm->update(['created_by' => $ulpUsers[$lm->location_id]]);
                
                // 4. Reassign all realizations for this Lead Measure to the same ULP user
                Realization::where('target_id', $lm->id)->update([
                    'created_by' => $ulpUsers[$lm->location_id]
                ]);
            }
        }
        
        // Optional: Reassign Sub-WIGs to their respective UP3 users
        $up3Users = [];
        foreach (User::whereHas('roles', function($q) { $q->where('name', 'admin_up3'); })->get() as $u) {
            $up3Users[$u->location_id] = $u->id;
        }
        
        $subWigs = Target::where('type', 'Sub-WIG')->get();
        foreach ($subWigs as $sw) {
            if (isset($up3Users[$sw->location_id])) {
                $sw->update(['created_by' => $up3Users[$sw->location_id]]);
            }
        }
    }
}
