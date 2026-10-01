CREATE TABLE IF NOT EXISTS import_records (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    city VARCHAR(100) NOT NULL DEFAULT 'Bern',
    species VARCHAR(30) NOT NULL,
    origin VARCHAR(150) NOT NULL,
    distance INT NOT NULL DEFAULT 0,
    amount DOUBLE NOT NULL DEFAULT 0,
    price DOUBLE NOT NULL DEFAULT 0,
    `year` SMALLINT NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_import_records_year (`year`),
    INDEX idx_import_records_origin (origin),
    INDEX idx_import_records_species (species)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
