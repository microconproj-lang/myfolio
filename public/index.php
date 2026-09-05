<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/app/Helpers/csrf.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

foreach ($_ENV as $key => $value) {
    putenv($key . '=' . $value);
}

$app = require dirname(__DIR__) . '/app/Config/app.php';
date_default_timezone_set($app['timezone']);
session_name($app['session_name']);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => parse_url($app['url'], PHP_URL_PATH) ?: '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");

$router = new MyFolio\Core\Router();
$router->get('/', [MyFolio\Controllers\PortfolioController::class, 'index']);
$router->get('/login', [MyFolio\Controllers\AuthController::class, 'login']);
$router->get('/auth/google', [MyFolio\Controllers\AuthController::class, 'googleRedirect']);
$router->get('/auth/google-callback.php', [MyFolio\Controllers\AuthController::class, 'googleCallback']);
$router->get('/auth/google-callback', [MyFolio\Controllers\AuthController::class, 'googleCallback']);
$router->get('/dashboard', [MyFolio\Controllers\AdminController::class, 'dashboard']);
$router->get('/portfolio/new', [MyFolio\Controllers\PortfolioController::class, 'create']);
$router->get('/portfolio/{id}/edit', [MyFolio\Controllers\PortfolioController::class, 'edit']);
$router->post('/portfolio', [MyFolio\Controllers\PortfolioController::class, 'store']);
$router->post('/portfolio/{id}', [MyFolio\Controllers\PortfolioController::class, 'update']);
$router->post('/portfolio/{id}/delete', [MyFolio\Controllers\PortfolioController::class, 'delete']);
$router->get('/portfolio', [MyFolio\Controllers\PortfolioController::class, 'index']);
$router->get('/file/view/{id}', [MyFolio\Controllers\FileController::class, 'view']);
$router->get('/logout', [MyFolio\Controllers\AuthController::class, 'logout']);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = parse_url($app['url'], PHP_URL_PATH) ?: '';
if ($basePath !== '' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath)) ?: '/';
}

echo $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', '/' . trim($path, '/'));