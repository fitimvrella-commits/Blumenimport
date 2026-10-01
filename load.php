<?php
/**
 * load.php – transformierte Daten in SQLite schreiben
 */

$dbFile = __DIR__ . '/blumenimport.sqlite';
$schemaFile = __DIR__ . '/schema.sql';

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!file_exists($schemaFile)) {
    die("schema.sql wurde nicht gefunden.\n");
}

$schemaSql = file_get_contents($schemaFile);
if ($schemaSql === false) {
    die("schema.sql konnte nicht gelesen werden.\n");
}

$pdo->exec($schemaSql);

$transformCandidates = [
    __DIR__ . '/Data/etl/data/transform.php',
    __DIR__ . '/etl/transform.php',
    __DIR__ . '/transform.php'
];

$transformFile = null;
foreach ($transformCandidates as $candidate) {
    if (file_exists($candidate)) {
        $transformFile = $candidate;
        break;
    }
}

if ($transformFile === null) {
    die("transform.php wurde nicht gefunden.\n");
}

ob_start();
$result = include $transformFile;
ob_end_clean();

$records = [];
if (is_array($result) && array_key_exists('data', $result)) {
    $records = $result['data'];
} elseif (is_array($result)) {
    $records = $result;
}

if (!is_array($records) || count($records) === 0) {
    echo "Keine Daten zum Laden vorhanden.\n";
    exit(0);
}

$insert = $pdo->prepare(
    'INSERT INTO import_records (species, origin, distance, amount, price, year)
     VALUES (:species, :origin, :distance, :amount, :price, :year)'
);

$inserted = 0;
foreach ($records as $record) {
    if (!is_array($record)) {
        continue;
    }

    if (!isset($record['species'], $record['origin'], $record['distance'], $record['year'])) {
        continue;
    }

    $insert->execute([
        ':species' => (string) ($record['species'] ?? ''),
        ':origin' => (string) ($record['origin'] ?? ''),
        ':distance' => (int) ($record['distance'] ?? 0),
        ':amount' => (float) ($record['amount'] ?? 0),
        ':price' => (float) ($record['price'] ?? 0),
        ':year' => (int) ($record['year'] ?? 0),
    ]);

    $inserted++;
}

echo "Load erfolgreich: $inserted Datensätze in $dbFile geschrieben.\n";
