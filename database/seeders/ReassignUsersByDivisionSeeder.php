<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Location;
use App\Models\Division;
use App\Models\Target;
use App\Models\Realization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ReassignUsersByDivisionSeeder extends Seeder
{
    public function run(): void
    {
        $roleUp3 = Role::where('name', 'admin_up3')->first();
        $roleUlp = Role::where('name', 'admin_ulp')->first();
        $roleUid = Role::where('name', 'admin_uid')->first();
        
        $divisions = Division::all();
        $up3s = Location::where('type', 'UP3')->get();
        $ulps = Location::where('type', 'ULP')->get();
        $uid = Location::where('type', 'UID')->first();

        // 1. Delete generic users that have no division_id
        User::whereNull('division_id')->where('email', '!=', 'superadmin@pln.co.id')->delete();

        // 2. Create Division-specific Users for ULP
        foreach ($ulps as $ulp) {
            foreach ($divisions as $div) {
                // Short name for division in email
                $divCode = Str::slug(explode(' ', $div->name)[0]);
                $email = 'admin.' . $divCode . '.' . Str::slug(str_replace('ULP ', '', $ulp->name)) . '@pln.co.id';
                
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => 'Admin ' . $div->name . ' - ' . $ulp->name,
                        'password' => Hash::make('password'),
                        'location_id' => $ulp->id,
                        'location_type' => 'ULP',
                        'division_id' => $div->id,
                    ]
                );
                
                if (!$user->roles()->where('role_id', $roleUlp->id)->exists()) {
                    $user->roles()->attach($roleUlp->id);
                }

                // Find Lead Measures in this ULP for this Division
                $leadMeasures = Target::where('type', 'Lead Measure')
                    ->where('location_id', $ulp->id)
                    ->whereHas('metric', function($q) use ($div) {
                        $q->where('division_id', $div->id);
                    })->get();

                foreach ($leadMeasures as $lm) {
                    $lm->update(['created_by' => $user->id]);
                    Realization::where('target_id', $lm->id)->update(['created_by' => $user->id]);
                }
            }
        }

        // 3. Create Division-specific Users for UP3
        foreach ($up3s as $up3) {
            foreach ($divisions as $div) {
                $divCode = Str::slug(explode(' ', $div->name)[0]);
                $email = 'manager.' . $divCode . '.' . Str::slug(str_replace('UP3 ', '', $up3->name)) . '@pln.co.id';
                
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => 'Manager ' . $div->name . ' - ' . $up3->name,
                        'password' => Hash::make('password'),
                        'location_id' => $up3->id,
                        'location_type' => 'UP3',
                        'division_id' => $div->id,
                    ]
                );
                
                if (!$user->roles()->where('role_id', $roleUp3->id)->exists()) {
                    $user->roles()->attach($roleUp3->id);
                }

                // Find Sub-WIGs in this UP3 for this Division
                $subWigs = Target::where('type', 'Sub-WIG')
                    ->where('location_id', $up3->id)
                    ->whereHas('metric', function($q) use ($div) {
                        $q->where('division_id', $div->id);
                    })->get();

                foreach ($subWigs as $sw) {
                    $sw->update(['created_by' => $user->id]);
                }
            }
        }

        // 4. Create Division-specific Users for UID (WIG Utama)
        if ($uid) {
            foreach ($divisions as $div) {
                $divCode = Str::slug(explode(' ', $div->name)[0]);
                $email = 'manager.' . $divCode . '.uid@pln.co.id';
                
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => 'Manager ' . $div->name . ' UID',
                        'password' => Hash::make('password'),
                        'location_id' => $uid->id,
                        'location_type' => 'UID',
                        'division_id' => $div->id,
                    ]
                );
                
                if (!$user->roles()->where('role_id', $roleUid->id)->exists()) {
                    $user->roles()->attach($roleUid->id);
                }

                // Find WIG Utama for this Division
                $wigs = Target::where('type', 'WIG Utama')
                    ->where('location_id', $uid->id)
                    ->whereHas('metric', function($q) use ($div) {
                        $q->where('division_id', $div->id);
                    })->get();

                foreach ($wigs as $w) {
                    $w->update(['created_by' => $user->id]);
                }
            }
        }
    }
}
