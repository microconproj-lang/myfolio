<?php

namespace MyFolio\Models;

use MyFolio\Core\Database;

final class PaCategory
{
    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM pa_categories ORDER BY assessment_part, sort_order, id')->fetchAll();
    }

    public static function ensureDefaults(): array
    {
        $database = Database::connection();
        $categories = self::all();
        if ($categories !== []) {
            return $categories;
        }

        $statement = $database->prepare('INSERT INTO pa_categories (assessment_part, name, description, sort_order) VALUES (:assessment_part, :name, :description, :sort_order)');
        $defaults = [
            ['part_1', 'ด้านการจัดการเรียนรู้', 'การออกแบบและจัดการเรียนรู้เพื่อพัฒนาผู้เรียน', 1],
            ['part_1', 'ด้านการส่งเสริมและสนับสนุนการจัดการเรียนรู้', 'การสนับสนุนระบบและสภาพแวดล้อมการเรียนรู้', 2],
            ['part_1', 'ด้านการพัฒนาตนเองและวิชาชีพ', 'การพัฒนาความรู้ ทักษะ และชุมชนวิชาชีพ', 3],
            ['part_2', 'ประเด็นท้าทาย', 'โจทย์พัฒนางานหรือนวัตกรรมที่ท้าทาย', 1],
            ['part_2', 'รายงานประเด็นท้าทาย', 'รายงานผลลัพธ์และบทเรียนจากประเด็นท้าทาย', 2],
        ];
        foreach ($defaults as [$assessmentPart, $name, $description, $sortOrder]) {
            $statement->execute(['assessment_part' => $assessmentPart, 'name' => $name, 'description' => $description, 'sort_order' => $sortOrder]);
        }

        return self::all();
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM pa_categories WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }
}