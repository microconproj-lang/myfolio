<?php

namespace MyFolio\Core;

abstract class Controller
{
    protected function requirePostCsrf(): void
    {
        $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
        $submittedToken = (string) ($_POST['_token'] ?? '');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $sessionToken === '' || $submittedToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            http_response_code(419);
            exit('Invalid form token');
        }
    }

    protected function view(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $view = __DIR__ . '/../Views/' . $template . '.php';
        require __DIR__ . '/../Views/layouts/main.php';
    }

    protected function redirect(string $path): never
    {
        $app = require __DIR__ . '/../Config/app.php';
        header('Location: ' . $app['url'] . '/' . ltrim($path, '/'));
        exit;
    }
}