<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\User::with('roles')->get();
echo "Total users: " . $users->count() . "\n";
foreach ($users as $u) {
    $roles = $u->roles->pluck('name')->join(', ');
    echo "ID: {$u->id} | {$u->firstname} {$u->lastname} | {$u->email} | RUT: {$u->rut} | Roles: [{$roles}]\n";
}

