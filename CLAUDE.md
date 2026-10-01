# Projektanweisung – IM3 Data Story: Schweizer Schnittblumenimporte

## Wer ich bin
Fitim (Dugi), Multimedia-Production-Student an der FHGR in Chur. Modul: Interaktive Medien 3 (IM3). Teampartner: Flo und Emily.

## Wie du arbeitest

### Grundregel
Ich treffe alle inhaltlichen Entscheidungen. Du codierst und setzt um. Aber ich muss verstehen, was der Code macht — erkläre mir bei jedem Schritt kurz und verständlich, was passiert und warum. Nicht wie ein Tutorial, sondern wie ein Teamkollege, der mir sagt: "Das hier macht X, weil Y."

### Was du darfst (gemäss Kursfolie)
- Schleifen und Hilfsfunktionen schreiben
- Ein langes Mapping ausformulieren
- Unbekannte Rohwerte gruppieren
- Audit-Zähler ergänzen
- Code kommentieren

### Was du NICHT entscheiden darfst
- Welche Datenfrage wir stellen
- Welche Fälle dazugehören
- Welche Kategorien es gibt
- Was mit unsicheren Werten passiert
- Welche Aussage die Story machen darf

Bei diesen Punkten fragst du mich und wartest auf meine Antwort.

### Kursregeln haben Vorrang
Alle Arbeiten müssen den FHGR/IM3-Vorgaben entsprechen. Wenn in den Kursunterlagen z.B. `echo` steht, verwenden wir `echo`. Wenn dort `return` steht, verwenden wir `return`. Keine eigenmächtigen Abweichungen. Wenn du unsicher bist, frag mich.

## Unser Projekt

### Datenfrage
Woher importiert die Schweiz ihre Schnittblumen, wie viel kosten sie, und wie weit reisen sie?

### Datenquelle
OEC API (Observatory of Economic Complexity) — Live-API, kostenlos, keine Authentifizierung nötig.

API-URL:
```
https://api-v2.oec.world/tesseract/data.jsonrecords?cube=trade_i_baci_a_92&drilldowns=HS6,Exporter+Country,Year&measures=Trade+Value,Quantity&Importer+Country=euche&HS4=20603
```

### Datenvertrag (Felder nach Transform)
Jeder Datensatz hat diese Struktur:
```json
{
  "species":  "frisch | getrocknet",
  "origin":   "Ländername",
  "distance": 629,
  "amount":   1234.5,
  "price":    5678.90,
  "year":     2023
}
```

- `species`: abgeleitet aus HS6-Feld ("Fresh" → frisch, "Dried" → getrocknet)
- `origin`: Exporter Country aus der API
- `distance`: Lookup-Tabelle (Hauptstadt des Landes → Bern, Luftlinie in km)
- `amount`: Quantity aus der API (Tonnen), fehlend → 0
- `price`: Trade Value / Quantity (USD pro Tonne), fehlend → 0
- `year`: Year aus der API

### ETL-Pipeline (5 Schritte)
```
Extract → Transform → Load → Unload → Visualization
```

1. **Extract** (extract.php) — Daten von der API holen → PHP-Array. FERTIG ✅
2. **Transform** (transform.php) — Regeln anwenden (umbenennen, normalisieren, ableiten, bereinigen) + Audit. FERTIG ✅
3. **Load** (load.php) — In die Datenbank schreiben. OFFEN ❌
4. **Unload** (unload.php) — JSON-Endpunkt für das Frontend. OFFEN ❌
5. **Visualization** (index.html + Chart.js/Leaflet) — Data Story im Browser. OFFEN ❌

### Kontrollansicht
- index.php zeigt das Transform-Ergebnis als JSON — ein Prüfwerkzeug, kein Produkt.

### Wie die PHP-Dateien zusammenhängen
```
extract.php    → return $records;              (PHP-Array mit Rohdaten)
transform.php  → $records = include 'extract.php';  → return ['data' => ..., 'audit' => ...];
index.php      → $result = include 'transform.php'; → echo json_encode($result);
```

### Ordnerstruktur
```
blumenimport/
├── etl/
│   ├── extract.php
│   ├── transform.php
│   └── index.php
├── schema.sql          (noch zu erstellen)
├── load.php            (noch zu erstellen)
├── unload.php          (noch zu erstellen)
└── index.html          (noch zu erstellen)
```

## Kursunterlagen
Die PDFs der Kursfolien lade ich bei Bedarf einzeln hoch. Frag mich, welche du brauchst, bevor du etwas umsetzt. Die wichtigsten sind:
- 26HS_IM3_B_extract.pdf (Extract-Schritt)
- 26HS_IM3_C_transform.pdf (Transform-Schritt)
- 26HS_IM3_D_load.pdf (Load-Schritt)
- 26HS_IM3_E_unload.pdf (Unload-Schritt)
- 26HS_IM3_F_visualisierung.pdf (Visualisierung)

## Nächster Schritt
Wir machen **load.php** — die transformierten Daten in eine Datenbank schreiben. Dazu brauchen wir zuerst ein schema.sql. Ich lade dir die Kursunterlage 26HS_IM3_D_load.pdf hoch, damit du weisst, wie es gemäss Kurs gemacht wird.
