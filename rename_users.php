<?php
$users = \App\Models\User::all();
foreach($users as $user) {
    $user->name = str_replace('Admin ', '', $user->name);
    $user->email = str_replace('admin.', '', $user->email);
    
    // Also remove Manager prefix just in case for consistency, but the user only explicitly asked for admin
    // We will only do what they asked.
    $user->save();
}
echo "Users updated successfully.\n";
