DROP TABLE IF EXISTS import_records;

CREATE TABLE import_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    city TEXT NOT NULL DEFAULT 'Bern',
    species TEXT NOT NULL,
    origin TEXT NOT NULL,
    distance INTEGER NOT NULL DEFAULT 0,
    amount REAL NOT NULL DEFAULT 0,
    price REAL NOT NULL DEFAULT 0,
    year INTEGER NOT NULL
);

CREATE INDEX idx_import_records_year ON import_records(year);
CREATE INDEX idx_import_records_origin ON import_records(origin);
CREATE INDEX idx_import_records_species ON import_records(species);
