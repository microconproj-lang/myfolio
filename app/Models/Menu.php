<?php

namespace MyFolio\Models;

use MyFolio\Core\Database;

final class Menu
{
    public static function visible(): array
    {
        return Database::connection()->query('SELECT * FROM menus WHERE is_visible = 1 ORDER BY sort_order, id')->fetchAll();
    }
}