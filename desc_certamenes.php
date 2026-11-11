<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = DB::select('DESCRIBE certamenes');
foreach ($cols as $c) {
    echo $c->Field . " (" . $c->Type . ")\n";
}

