<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$envios = App\Models\EnvioSolucionProblema::orderBy('id', 'desc')->take(5)->get();

foreach ($envios as $e) {
    echo "ID: " . $e->id . "\n";
    echo "Problema: " . ($e->problema ? $e->problema->nombre : 'N/A') . "\n";
    echo "Cumple restricciones: " . var_export($e->cumple_restricciones, true) . "\n";
    echo "Verificacion restricciones:\n" . $e->verificacion_restricciones . "\n";
    echo "Codigo:\n" . substr($e->codigo, 0, 200) . "...\n";
    echo "-------------------------------------------\n";
}

