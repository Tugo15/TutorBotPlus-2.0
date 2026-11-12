<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$totalUsers = App\Models\User::count();
echo "Total usuarios en BD: " . $totalUsers . "\n";

$cursos = App\Models\Cursos::withCount('users')->get();
foreach ($cursos as $c) {
    echo "Curso [{$c->id}] {$c->nombre} ({$c->codigo}): {$c->users_count} usuarios\n";
}

$admins = App\Models\User::role('administrador')->get(['id', 'firstname', 'lastname', 'email']);
echo "Total administradores: " . $admins->count() . "\n";

$roles = Spatie\Permission\Models\Role::withCount('users')->get();
foreach ($roles as $r) {
    echo "Rol [{$r->name}]: {$r->users_count} usuarios\n";
}
