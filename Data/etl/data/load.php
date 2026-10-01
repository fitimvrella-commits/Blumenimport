<?php
/**
 * load.php – Transform-Daten in die SQLite-Datenbank schreiben.
 */

header('Content-Type: text/plain; charset=utf-8');

// Das Ergebnis des Transforms holen und seine Kontrollausgaben ausblenden.
ob_start();
try {
    $result = include __DIR__ . '/transform.php';
} finally {
    ob_end_clean();
}

if (!is_array($result) || !isset($result['data']) || !is_array($result['data'])) {
    exit("Der Transform hat kein gueltiges data-Array zurueckgegeben.\n");
}

$rows = $result['data'];
echo 'Der Transform liefert ' . count($rows) . " Zeilen.\n\n";

if (count($rows) === 0) {
    exit("Keine Daten zum Laden vorhanden.\n");
}

$requiredFields = ['species', 'origin', 'distance', 'amount', 'price', 'year'];
foreach ($rows as $index => $row) {
    if (!is_array($row)) {
        exit("Datensatz $index ist kein Array.\n");
    }

    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $row)) {
            exit("Datensatz $index enthaelt das Pflichtfeld '$field' nicht.\n");
        }
    }
}

// Datenbank und Schema liegen im Hauptordner des Projekts.
$projectDirectory = dirname(__DIR__, 3);
$dbFile = $projectDirectory . '/blumenimport.sqlite';
$schemaFile = $projectDirectory . '/schema.sql';

if (!file_exists($schemaFile)) {
    exit("schema.sql wurde nicht gefunden.\n");
}

$schemaSql = file_get_contents($schemaFile);
if ($schemaSql === false) {
    exit("schema.sql konnte nicht gelesen werden.\n");
}

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec($schemaSql);
    echo "Verbindung steht.\n\n";
} catch (PDOException $e) {
    exit('Verbindung oder Schema fehlgeschlagen: ' . $e->getMessage() . "\n");
}

// Die API liefert den vollständigen verfügbaren Zeitraum; deshalb wird der
// Datenstand wie im Code-Along ersetzt, statt bei jedem Lauf Duplikate anzuhängen.
$insert = $pdo->prepare(
    'INSERT INTO import_records (species, origin, distance, amount, price, year)
     VALUES (:species, :origin, :distance, :amount, :price, :year)'
);

try {
    $pdo->beginTransaction();
    $deleted = $pdo->exec('DELETE FROM import_records');

    foreach ($rows as $row) {
        $insert->execute([
            ':species' => $row['species'],
            ':origin' => $row['origin'],
            ':distance' => $row['distance'],
            ':amount' => $row['amount'],
            ':price' => $row['price'],
            ':year' => $row['year'],
        ]);
    }

    $pdo->commit();
    echo $deleted . " alte Zeilen geloescht.\n\n";
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    exit('Schreiben fehlgeschlagen: ' . $e->getMessage() . "\n");
}

echo count($rows) . " Zeilen geschrieben.\n\n";

// Die Zeilenanzahl aus der Datenbank kontrollieren und aktuelle Einträge zeigen.
$total = $pdo->query('SELECT COUNT(*) FROM import_records')->fetchColumn();
echo "In import_records stehen jetzt {$total} Zeilen.\n\n";

$latestRecords = $pdo->query(
    'SELECT origin, year, species, amount, price, distance
     FROM import_records
     ORDER BY year DESC, origin
     LIMIT 5'
);

echo "Fuenf aktuelle Eintraege:\n";
foreach ($latestRecords->fetchAll(PDO::FETCH_ASSOC) as $record) {
    echo '  ' . $record['year'] . ' | ' . $record['origin'] . ' | '
        . $record['species'] . ' | ' . $record['amount'] . ' t | '
        . $record['price'] . ' USD/t | ' . $record['distance'] . " km\n";
}
