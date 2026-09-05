<?php

namespace MyFolio\Models;

use MyFolio\Core\Database;

final class EvaluationComment
{
    public static function forItem(int $itemId): array
    {
        $statement = Database::connection()->prepare('SELECT * FROM evaluation_comments WHERE pa_item_id = :item_id ORDER BY created_at DESC');
        $statement->execute(['item_id' => $itemId]);
        return $statement->fetchAll();
    }
}