<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

$rolesToRename = [
    'Perencanaan UP2D' => 'UP2D',
    'Perencanaan UP2K' => 'UP2K',
];

foreach ($rolesToRename as $oldName => $newName) {
    $oldRole = Role::where('name', $oldName)->first();
    $newRole = Role::where('name', $newName)->first();

    if ($oldRole && $newRole) {
        // Both exist, so re-assign users from old to new, then delete old
        $users = User::role($oldName)->get();
        foreach ($users as $user) {
            $user->removeRole($oldName);
            $user->assignRole($newName);
        }
        $oldRole->delete();
        echo "Merged $oldName into $newName\n";
    } elseif ($oldRole && !$newRole) {
        // Only old exists, just rename it
        $oldRole->name = $newName;
        $oldRole->save();
        echo "Renamed $oldName to $newName\n";
    } elseif (!$oldRole && !$newRole) {
        Role::create(['name' => $newName]);
        echo "Created $newName\n";
    } else {
        echo "$newName already exists and $oldName is not found. All good.\n";
    }
}
