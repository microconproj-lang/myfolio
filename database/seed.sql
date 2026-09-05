USE myfolio;
INSERT INTO roles (name) VALUES ('admin'), ('committee'), ('public')
ON DUPLICATE KEY UPDATE name = VALUES(name);
INSERT INTO menus (label, slug, sort_order) VALUES
    ('ส่วนที่ 1 · มาตรฐานตำแหน่ง', 'standard-position', 1),
    ('ส่วนที่ 2 · ประเด็นท้าทาย', 'challenge', 2)
ON DUPLICATE KEY UPDATE label = VALUES(label);