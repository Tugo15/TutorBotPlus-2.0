<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$certs = App\Models\Certamenes::all();
foreach ($certs as $c) {
    echo "Certamen ID: " . $c->id . " | Nombre: " . $c->nombre . " | Cantidad problemas: " . $c->cantidad_problemas . "\n";
    $banco = DB::table('banco_problemas_certamenes')->where('id_certamen', $c->id)->get();
    echo "  Banco registros (" . count($banco) . "):\n";
    foreach ($banco as $b) {
        $cat = App\Models\Categoria_Problema::find($b->id_categoria);
        echo "    - Categoria ID: " . $b->id_categoria . " (" . ($cat ? $cat->nombre : 'N/A') . ")\n";
    }
}

