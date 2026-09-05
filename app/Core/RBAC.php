<?php

namespace MyFolio\Core;

final class RBAC
{
    public static function require(string ...$roles): void
    {
        $user = Auth::user();
        if ($user === null || !in_array($user['role'] ?? '', $roles, true)) {
            http_response_code($user === null ? 401 : 403);
            exit($user === null ? 'Authentication required' : 'Forbidden');
        }
    }
}