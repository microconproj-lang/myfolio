<?php

namespace MyFolio\Models;

use MyFolio\Core\Database;

final class User
{
    public static function upsertGoogle(string $googleSub, string $email, string $name, ?string $avatar, string $role): array
    {
        $database = Database::connection();
        $roleStatement = $database->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
        $roleStatement->execute(['name' => $role]);
        $roleId = $roleStatement->fetchColumn();
        if ($roleId === false) {
            throw new \RuntimeException('Unknown role: ' . $role);
        }

        $statement = $database->prepare(
            'INSERT INTO users (role_id, google_sub, email, name, avatar_url) VALUES (:role_id, :google_sub, :email, :name, :avatar_url)
             ON DUPLICATE KEY UPDATE role_id = VALUES(role_id), name = VALUES(name), avatar_url = VALUES(avatar_url)'
        );
        $statement->execute([
            'role_id' => $roleId,
            'google_sub' => $googleSub,
            'email' => $email,
            'name' => $name,
            'avatar_url' => $avatar,
        ]);

        return self::findByEmail($email) ?? throw new \RuntimeException('Unable to load authenticated user');
    }

    public static function findByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        return $statement->fetch() ?: null;
    }
}