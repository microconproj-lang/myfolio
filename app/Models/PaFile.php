<?php

namespace MyFolio\Models;

use MyFolio\Core\Database;

final class PaFile
{
    public static function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO pa_files (pa_item_id, original_name, storage_path, mime_type, file_size, page_count, uploaded_by)
             VALUES (:pa_item_id, :original_name, :storage_path, :mime_type, :file_size, :page_count, :uploaded_by)'
        );
        $statement->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function findAccessible(int $id, bool $publishedOnly): ?array
    {
        $query = 'SELECT pa_files.*, pa_items.status AS item_status FROM pa_files INNER JOIN pa_items ON pa_items.id = pa_files.pa_item_id WHERE pa_files.id = :id';
        if ($publishedOnly) {
            $query .= ' AND pa_items.status = "published"';
        }
        $query .= ' LIMIT 1';
        $statement = Database::connection()->prepare($query);
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public static function forItem(int $itemId): array
    {
        $statement = Database::connection()->prepare('SELECT * FROM pa_files WHERE pa_item_id = :item_id ORDER BY id');
        $statement->execute(['item_id' => $itemId]);
        return $statement->fetchAll();
    }
}