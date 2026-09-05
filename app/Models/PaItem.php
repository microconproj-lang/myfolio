<?php

namespace MyFolio\Models;

use MyFolio\Core\Database;

final class PaItem
{
    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT pa_items.*, pa_categories.name AS category_name, pa_categories.assessment_part
             FROM pa_items INNER JOIN pa_categories ON pa_categories.id = pa_items.category_id
             WHERE pa_items.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public static function all(): array
    {
        return Database::connection()->query(
            'SELECT pa_items.*, pa_categories.name AS category_name, users.name AS author_name
             FROM pa_items
             INNER JOIN pa_categories ON pa_categories.id = pa_items.category_id
             LEFT JOIN users ON users.id = pa_items.created_by
             ORDER BY pa_items.evaluation_year DESC, pa_items.evaluation_round, pa_categories.sort_order, pa_items.updated_at DESC, pa_items.id DESC'
        )->fetchAll();
    }

    public static function published(): array
    {
        return Database::connection()->query(
                'SELECT pa_items.*, pa_categories.name AS category_name, pa_categories.sort_order AS category_sort_order,
                    (SELECT id FROM pa_files WHERE pa_files.pa_item_id = pa_items.id AND mime_type IN ("image/jpeg", "image/png", "image/webp") ORDER BY id LIMIT 1) AS thumbnail_file_id
             FROM pa_items INNER JOIN pa_categories ON pa_categories.id = pa_items.category_id
             WHERE pa_items.status = "published" ORDER BY pa_items.evaluation_year DESC, pa_items.evaluation_round, pa_categories.sort_order, pa_items.sort_order, pa_items.id DESC'
        )->fetchAll();
    }

    public static function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO pa_items (category_id, evaluation_year, evaluation_round, title, description, github_url, status, created_by)
             VALUES (:category_id, :evaluation_year, :evaluation_round, :title, :description, :github_url, :status, :created_by)'
        );
        $statement->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $statement = Database::connection()->prepare(
            'UPDATE pa_items SET category_id = :category_id, evaluation_year = :evaluation_year,
             evaluation_round = :evaluation_round, title = :title, description = :description,
             github_url = :github_url, status = :status WHERE id = :id'
        );
        $statement->execute($data);
    }

    public static function delete(int $id): void
    {
        $statement = Database::connection()->prepare('DELETE FROM pa_items WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}