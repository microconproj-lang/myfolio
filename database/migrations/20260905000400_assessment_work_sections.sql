USE myfolio;

ALTER TABLE pa_categories
    ADD COLUMN assessment_part ENUM('part_1', 'part_2') NOT NULL DEFAULT 'part_1' AFTER id;

UPDATE pa_categories SET
    name = 'ด้านการจัดการเรียนรู้',
    description = 'การออกแบบและจัดการเรียนรู้เพื่อพัฒนาผู้เรียน',
    assessment_part = 'part_1',
    sort_order = 1
WHERE id = 1;

UPDATE pa_categories SET
    name = 'ประเด็นท้าทาย',
    description = 'โจทย์พัฒนางานหรือนวัตกรรมที่ท้าทาย',
    assessment_part = 'part_2',
    sort_order = 1
WHERE id = 2;

INSERT INTO pa_categories (assessment_part, name, description, sort_order)
SELECT 'part_1', 'ด้านการส่งเสริมและสนับสนุนการจัดการเรียนรู้', 'การสนับสนุนระบบและสภาพแวดล้อมการเรียนรู้', 2
WHERE NOT EXISTS (SELECT 1 FROM pa_categories WHERE name = 'ด้านการส่งเสริมและสนับสนุนการจัดการเรียนรู้');

INSERT INTO pa_categories (assessment_part, name, description, sort_order)
SELECT 'part_1', 'ด้านการพัฒนาตนเองและวิชาชีพ', 'การพัฒนาความรู้ ทักษะ และชุมชนวิชาชีพ', 3
WHERE NOT EXISTS (SELECT 1 FROM pa_categories WHERE name = 'ด้านการพัฒนาตนเองและวิชาชีพ');

INSERT INTO pa_categories (assessment_part, name, description, sort_order)
SELECT 'part_2', 'รายงานประเด็นท้าทาย', 'รายงานผลลัพธ์และบทเรียนจากประเด็นท้าทาย', 2
WHERE NOT EXISTS (SELECT 1 FROM pa_categories WHERE name = 'รายงานประเด็นท้าทาย');