USE myfolio;

ALTER TABLE pa_items
    ADD COLUMN evaluation_year SMALLINT UNSIGNED NOT NULL DEFAULT 2569 AFTER category_id,
    ADD COLUMN evaluation_round ENUM('salary_march', 'vpa_september') NOT NULL DEFAULT 'vpa_september' AFTER evaluation_year;

ALTER TABLE pa_items
    ALTER COLUMN evaluation_year DROP DEFAULT,
    ALTER COLUMN evaluation_round DROP DEFAULT;