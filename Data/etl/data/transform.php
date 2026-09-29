<?php
/**
 * transform.php – Regeln auf die Rohdaten anwenden
 *
 * Gemäss Kursmaterial (Folie 28–30):
 * - extract.php liefert das Roh-Array per return
 * - transform.php wendet die Regeln an und gibt das Ergebnis per return weiter
 * - index.php zeigt das Ergebnis als JSON (Kontrollansicht)
 *
 * Aktuell: Durchreiche ohne Transformation – zum Testen der Kette.
 * TODO: Regeln anwenden (umbenennen, distance hinzufügen, etc.)
 */

// ── Rohdaten von extract.php holen (Folie 30) ──────────────────────────────
$records = include __DIR__ . '/extract.php';

// ── Kontrolle ───────────────────────────────────────────────────────────────
echo "\nTransform: " . count($records) . " Datensätze erhalten.\n";

// ── Rückgabe: PHP-Array für index.php ───────────────────────────────────────
return $records;