<?php
header('Content-Type: text/plain; charset=utf-8');
/**
 * extract.php – Schnittblumenimporte der Schweiz aus der OEC API lesen
 *
 * Quelle:  OEC (Observatory of Economic Complexity) – BACI HS92
 * Cube:    trade_i_baci_a_92  (1995–2024)
 * Ziel:    PHP-Array mit allen Datensätzen → wird an transform.php weitergegeben
 */

// ── fetchJson – cURL-Helfer gemäss IM3-Kursmaterial ──────────────────────
function fetchJson(string $url): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);

    $httpCode = 0;
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        die("cURL-Fehler: $error\n");
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        die("HTTP-Fehler: Status $httpCode\n");
    }
    $data = json_decode($response, true);

    if ($data === null) {
        die("JSON-Dekodierung fehlgeschlagen.");
    }

    return $data;
}

// ── API-Aufruf ───────────────────────────────────────────────────────────
$url = 'https://api-v2.oec.world/tesseract/data.jsonrecords'
    . '?cube=trade_i_baci_a_92'
    . '&drilldowns=HS6,Exporter+Country,Year'
    . '&measures=Trade+Value,Quantity'
    . '&Importer+Country=euche'
    . '&HS4=20603';

$json = fetchJson($url);

// ── Kontrolle: Haben wir Daten? ──────────────────────────────────────────
if (!isset($json['data']) || count($json['data']) === 0) {
    die("Keine Daten von der API erhalten.");
}

$records = $json['data'];

echo "Extract erfolgreich: " . count($records) . " Datensätze geladen.\n";
echo "Zeitraum: " . min(array_column($records, 'Year'))
    . " – "       . max(array_column($records, 'Year')) . "\n";

// ── Ergebnis: $records ist das PHP-Array für den nächsten Schritt ────────
// Beispiel: ersten 3 Datensätze ausgeben
echo "\nBeispiel "alle Einträge":\n";
for ($i = 0; $i < count($records); $i++) {
    $r = $records[$i];
    echo "  " . $r['Year'] . " | "
        . str_pad($r['Exporter Country'], 20)
        . " | HS6: " . $r['HS6']
        . " | Menge: " . ($r['Quantity'] ?? 'n/a') . " t"
        . " | Wert: " . number_format($r['Trade Value'], 0, '.', "'") . " USD\n";
}

// ── Rückgabe: PHP-Array für transform.php (gemäss Kursmaterial Folie 30) ──
return $records;