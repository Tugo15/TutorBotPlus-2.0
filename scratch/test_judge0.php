<?php
require __DIR__ . '/../vendor/autoload.php';

$client = new \GuzzleHttp\Client(['timeout' => 5]);

echo "--- Probando conexión LOCAL (http://127.0.0.1:2358) ---\n";
$start = microtime(true);
try {
    $res = $client->get('http://127.0.0.1:2358/about');
    $elapsed = (microtime(true) - $start) * 1000;
    echo "Respuesta OK en {$elapsed} ms. HTTP {$res->getStatusCode()}\n";
    echo "Body: " . substr($res->getBody(), 0, 100) . "\n\n";
} catch (\Exception $e) {
    echo "ERROR en local: " . $e->getMessage() . "\n\n";
}

echo "--- Probando conexión REMOTA (https://ce.judge0.com) ---\n";
$start = microtime(true);
try {
    $res = $client->get('https://ce.judge0.com/about');
    $elapsed = (microtime(true) - $start) * 1000;
    echo "Respuesta OK en {$elapsed} ms. HTTP {$res->getStatusCode()}\n";
    echo "Body: " . substr($res->getBody(), 0, 100) . "\n\n";
} catch (\Exception $e) {
    echo "ERROR en remoto: " . $e->getMessage() . "\n\n";
}
