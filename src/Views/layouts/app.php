<?php
/** @var array $config */
/** @var string $content */
/** @var string $title */
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang ?? 'de', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? $config['app']['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/assets/app.css">
</head>
<body>
<header class="site-header">
    <div class="container site-header-inner">
        <a class="brand" href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/"><?= htmlspecialchars($config['app']['name'], ENT_QUOTES, 'UTF-8') ?></a>
        <nav class="nav">
            <a href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/">Roster</a>
            <a href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/admin/players">Admin</a>
        </nav>
    </div>
</header>

<main class="container">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container site-footer-inner">
        <span class="muted">Lean PHP MVP for roster management</span>
        <span class="muted">Built for Plesk-style hosting</span>
    </div>
</footer>
</body>
</html>
