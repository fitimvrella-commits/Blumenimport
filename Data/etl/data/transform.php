<?php
/**
 * transform.php – Rohdaten bereinigen und in den Projekt-Datenvertrag überführen
 */

function normalizeSpecies($value): ?string
{
    // Prueft den ganzen HS6-Text ohne Beachtung der Gross-/Kleinschreibung.
    $value = (string) $value;

    if (stripos($value, 'fresh') !== false) {
        return 'frisch';
    }

    if (stripos($value, 'dried') !== false) {
        return 'getrocknet';
    }

    // Ohne Fresh oder Dried gibt die Funktion null zurueck; die Zeile wird
    // in der Verarbeitungsschleife gezaehlt und aussortiert.
    return null;
}

function toFloat($value): float
{
    // Macht API-Werte zu Zahlen; fehlende oder ungueltige Werte werden zu 0.
    if ($value === null || $value === '') {
        return 0.0;
    }

    if (is_string($value)) {
        $value = str_replace(',', '.', trim($value));
    }

    if (!is_numeric($value)) {
        return 0.0;
    }

    return (float) $value;
}

function lookupDistanceKm(string $country): int
{
    // Verifizierte Hauptstadt-zu-Bern-Distanzen aus der Team-Pruefliste:
    // Haversine-Formel, Erdradius 6371 km, Bern: 46.9480 N, 7.4474 E.
    $lookup = [
        'Albania' => 1166,
        'Argentina' => 11192,
        'Armenia' => 3048,
        'Australia' => 16594,
        'Austria' => 684,
        'Azerbaijan' => 3444,
        'Bangladesh' => 7583,
        'Belgium' => 489,
        'BelgiumLuxembourg' => 489,
        'Benin' => 4521,
        'Bhutan' => 7250,
        'Bolivia' => 10290,
        'Brazil' => 8889,
        'British Virgin Islands' => 7173,
        'Bulgaria' => 1335,
        'Burkina Faso' => 3935,
        'Cambodia' => 9629,
        'Cameroon' => 4808,
        'Canada' => 6084,
        'Cape Verde' => 4567,
        'Cayman Islands' => 8367,
        'Chad' => 3936,
        'Chile' => 11852,
        'China' => 8076,
        'Chinese Taipei' => 9648,
        'Colombia' => 8982,
        'Costa Rica' => 9319,
        "Cote d'Ivoire" => 4624,
        'Croatia' => 666,
        'Cuba' => 8140,
        'Cyprus' => 2520,
        'Czechia' => 621,
        'Denmark' => 1033,
        'Dominican Republic' => 7571,
        'Ecuador' => 9712,
        'Egypt' => 2776,
        'Estonia' => 1794,
        'Ethiopia' => 5150,
        'Finland' => 1858,
        'France' => 435,
        'French Polynesia' => 16127,
        'Gabon' => 5178,
        'Germany' => 753,
        'Ghana' => 4655,
        'Greece' => 1661,
        'Grenada' => 7457,
        'Guatemala' => 9413,
        'Haiti' => 7742,
        'Hong Kong' => 9396,
        'Hungary' => 877,
        'Iceland' => 2614,
        'India' => 6242,
        'Indonesia' => 11224,
        'Iran' => 3820,
        'Ireland' => 1205,
        'Israel' => 2899,
        'Italy' => 689,
        'Japan' => 9666,
        'Kenya' => 6079,
        'Kuwait' => 3986,
        'Kyrgyzstan' => 5141,
        'Laos' => 8940,
        'Latvia' => 1586,
        'Lebanon' => 2761,
        'Libya' => 1637,
        'Lithuania' => 1513,
        'Luxembourg' => 312,
        'Macau' => 9364,
        'Madagascar' => 8345,
        'Malawi' => 7264,
        'Malaysia' => 10074,
        'Maldives' => 7869,
        'Mali' => 4079,
        'Malta' => 1361,
        'Mauritania' => 3857,
        'Mauritius' => 8986,
        'Mexico' => 9629,
        'Moldova' => 1620,
        'Morocco' => 1877,
        'Nepal' => 6913,
        'Netherlands' => 630,
        'New Caledonia' => 16687,
        'New Zealand' => 18826,
        'Nicaragua' => 9302,
        'Niger' => 3751,
        'Nigeria' => 4211,
        'Niue' => 16904,
        'North Macedonia' => 1236,
        'Norway' => 1458,
        'Pakistan' => 5583,
        'Palestine' => 2886,
        'Panama' => 9050,
        'Peru' => 10572,
        'Philippines' => 10515,
        'Poland' => 1138,
        'Portugal' => 1627,
        'Qatar' => 4551,
        'Republic of the Congo' => 5746,
        'Romania' => 1472,
        'Russia' => 2289,
        'Saint Kitts and Nevis' => 7116,
        'Saudi Arabia' => 4247,
        'Senegal' => 4262,
        'Serbia' => 1034,
        'Seychelles' => 7419,
        'Singapore' => 10380,
        'Slovakia' => 737,
        'Slovenia' => 549,
        'South Africa' => 9059,
        'South Korea' => 8862,
        'Spain' => 1152,
        'Sri Lanka' => 8114,
        'Sudan' => 4195,
        'Suriname' => 7479,
        'Sweden' => 1544,
        'Tanzania' => 6745,
        'Thailand' => 9123,
        'Togo' => 4573,
        'Trinidad and Tobago' => 7527,
        'Tunisia' => 1150,
        'Turkey' => 2183,
        'Uganda' => 5727,
        'Ukraine' => 1730,
        'United Arab Emirates' => 4823,
        'United Kingdom' => 747,
        'United States' => 6598,
        'United States Minor Outlying Islands' => 12361,
        'Uruguay' => 11091,
        'Venezuela' => 7973,
        'Vietnam' => 8924,
        'Yugoslavia' => 1034,
        'Zambia' => 7238,
        'Zimbabwe' => 7580,
    ];

    $country = trim($country);

    if (isset($lookup[$country])) {
        return (int) $lookup[$country];
    }

    // 0 macht ein fehlendes Land im Audit sichtbar; laut Datenvertrag
    // sollte das mit der vollstaendigen Lookup-Tabelle nicht vorkommen.
    return 0;
}

// Extract-Daten einlesen und die echo-Ausgaben von extract.php unterdruecken.
ob_start();
$records = include __DIR__ . '/extract.php';
ob_end_clean();

// Audit-Zaehler dokumentieren Eingang, verworfene/fehlende Werte und Ergebnis.
$audit = [
    'input_rows' => count($records),
    'unknown_species' => 0,
    'missing_quantity' => 0,
    'missing_distance' => 0,
    'output_rows' => 0,
];

$cleaned = [];

foreach ($records as $row) {
    // Erwartet, dass HS6 direkt "Fresh" oder "Dried" enthaelt.
    $species = normalizeSpecies($row['HS6'] ?? '');

    if ($species === null) {
        $audit['unknown_species']++;
        continue;
    }

    $origin = trim((string) ($row['Exporter Country'] ?? ''));
    $rawAmount = $row['Quantity'] ?? null;

    // Zeilen ohne Quantity, mit null oder leerem Wert werden aussortiert.
    if (!array_key_exists('Quantity', $row) || $rawAmount === null || (is_string($rawAmount) && trim($rawAmount) === '')) {
        $audit['missing_quantity']++;
        continue;
    }

    $amount = toFloat($rawAmount);
    $tradeValue = toFloat($row['Trade Value'] ?? 0);

    $distance = lookupDistanceKm($origin);
    if ($distance === 0) {
        $audit['missing_distance']++;
    }

    // Preis = Handelswert pro Tonne; bei Menge 0 wird laut Datenvertrag 0 gesetzt.
    $price = ($amount > 0) ? round($tradeValue / $amount, 2) : 0.0;

    $cleaned[] = [
        'species' => $species,
        'origin' => $origin,
        'distance' => $distance,
        'amount' => round($amount, 2),
        'price' => round($price, 2),
        'year' => (int) ($row['Year'] ?? 0),
    ];
}

$audit['output_rows'] = count($cleaned);

// Das bereinigte Datenarray und die Audit-Zahlen werden gemeinsam zurueckgegeben.
return [
    'data' => $cleaned,
    'audit' => $audit,
];