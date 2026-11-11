<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = DB::select('DESCRIBE seleccion_problemas_certamen');
foreach ($cols as $c) {
    echo $c->Field . " (" . $c->Type . ")\n";
}

