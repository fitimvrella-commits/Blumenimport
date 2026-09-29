<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain; charset=utf-8');

ob_start();
$result = include __DIR__ . '/transform.php';
$echoOutput = ob_get_clean();

echo "=== DEBUG ===\n";
echo "Echo-Ausgaben: " . strlen($echoOutput) . " Zeichen\n";
echo "Typ von result: " . gettype($result) . "\n";

if (is_array($result)) {
    echo "Anzahl Datensätze: " . count($result) . "\n";
    echo "\nErste 2 Einträge:\n";
    print_r(array_slice($result, 0, 2));
} else {
    echo "result ist KEIN Array, sondern: ";
    var_dump($result);
}