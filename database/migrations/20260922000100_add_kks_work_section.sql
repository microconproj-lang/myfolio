USE myfolio;

INSERT INTO pa_categories (assessment_part, name, description, sort_order)
SELECT 'part_1', 'ภาระงาน ตามที่ก.ค.ศ. กำหนด', 'ภาระงานตามหลักเกณฑ์ที่ ก.ค.ศ. กำหนด', 4
WHERE NOT EXISTS (
    SELECT 1 FROM pa_categories WHERE name = 'ภาระงาน ตามที่ก.ค.ศ. กำหนด'
);