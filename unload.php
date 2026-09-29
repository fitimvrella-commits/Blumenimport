<?php
/**
 * index.php – Kontrollansicht (gemäss Kursmaterial Folie 32)
 *
 * Zeigt das Ergebnis von transform.php als JSON im Browser.
 * Das ist ein Prüfwerkzeug für uns, kein Produkt für die Website.
 */

header('Content-Type: application/json; charset=utf-8');

$result = include __DIR__ . '/transform.php';

echo json_encode(
    $result,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);