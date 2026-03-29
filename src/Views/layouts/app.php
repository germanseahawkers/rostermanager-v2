<?php
/** @var array $config */
/** @var string $content */
/** @var string $title */
$theme = $theme ?? ($config['team']['colors'] ?? []);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang ?? 'en', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? $config['app']['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        :root {
            --primary: <?= htmlspecialchars($theme['primary'] ?? '#0b2545', ENT_QUOTES, 'UTF-8') ?>;
            --secondary: <?= htmlspecialchars($theme['secondary'] ?? '#7ac143', ENT_QUOTES, 'UTF-8') ?>;
            --bg: <?= htmlspecialchars($theme['surface'] ?? '#eef3f8', ENT_QUOTES, 'UTF-8') ?>;
            --surface: <?= htmlspecialchars($theme['surface'] ?? '#ffffff', ENT_QUOTES, 'UTF-8') ?>;
            --surface-alt: <?= htmlspecialchars($theme['surface_alt'] ?? '#d7e4f0', ENT_QUOTES, 'UTF-8') ?>;
            --text: <?= htmlspecialchars($theme['text'] ?? '#f7fbff', ENT_QUOTES, 'UTF-8') ?>;
            --ink: <?= htmlspecialchars($theme['ink'] ?? '#142033', ENT_QUOTES, 'UTF-8') ?>;
            --muted: <?= htmlspecialchars($theme['muted'] ?? '#60708a', ENT_QUOTES, 'UTF-8') ?>;
            --line: <?= htmlspecialchars($theme['line'] ?? '#b5c5d6', ENT_QUOTES, 'UTF-8') ?>;
        }
    </style>
    <link rel="stylesheet" href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/assets/app.css">
</head>
<body>
<?php $ui = translations($lang ?? 'en'); ?>
<header class="site-header">
    <div class="container site-header-inner">
        <a class="brand" href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/"><?= htmlspecialchars($config['app']['name'], ENT_QUOTES, 'UTF-8') ?></a>
        <nav class="nav">
            <a href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/"><?= htmlspecialchars($ui['nav_roster'], ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/admin/players"><?= htmlspecialchars($ui['nav_admin'], ENT_QUOTES, 'UTF-8') ?></a>
        </nav>
    </div>
</header>

<main class="container site-shell">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container site-footer-inner">
        <span class="muted"><?= htmlspecialchars($ui['built_with'], ENT_QUOTES, 'UTF-8') ?></span>
        <span class="muted"><?= htmlspecialchars($ui['made_for'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
</footer>
</body>
</html>
