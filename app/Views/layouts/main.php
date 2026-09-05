<?php
$app = require __DIR__ . '/../../Config/app.php';
?><!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? $app['name'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($app['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/assets/css/app.css">
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <?php require __DIR__ . '/partials/navbar.php'; ?>
    <main class="mx-auto max-w-7xl px-5 py-10 sm:px-8">
        <?php require $view; ?>
    </main>
</body>
</html>