<?php
/** @var array $config */
/** @var string $content */
/** @var string $title */
$theme = $theme ?? ($config['team']['colors'] ?? []);
$club = $config['club'] ?? [];
$clubName = trim((string) ($club['name'] ?? ''));
$clubLogoPath = trim((string) ($club['logo_path'] ?? ''));
$brandHref = ($config['app']['base_path'] ?? '') . '/';
$clubLogoUrl = $clubLogoPath !== '' ? public_asset_url($clubLogoPath, $config) : '';
$metaDescription = trim((string) ($metaDescription ?? ''));
$canonicalUrl = trim((string) ($canonicalUrl ?? ''));
$ogType = trim((string) ($ogType ?? 'website'));
$ogImageUrl = trim((string) ($ogImageUrl ?? ''));
$metaTitle = trim((string) ($metaTitle ?? ($title ?? $config['app']['name'])));
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang ?? 'en', ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? $config['app']['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <?php if ($metaDescription !== ''): ?>
        <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
        <meta name="twitter:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if ($canonicalUrl !== ''): ?>
        <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta property="og:type" content="<?= htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($config['app']['name'], ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($ogImageUrl !== ''): ?>
        <meta property="og:image" content="<?= htmlspecialchars($ogImageUrl, ENT_QUOTES, 'UTF-8') ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars($ogImageUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
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
        <a class="brand-lockup" href="<?= htmlspecialchars($brandHref, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($clubLogoUrl !== ''): ?>
                <img class="brand-logo" src="<?= htmlspecialchars($clubLogoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($clubName !== '' ? $clubName : $config['app']['name'], ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <span class="brand-copy">
                <span class="brand"><?= htmlspecialchars($config['app']['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php if ($clubName !== ''): ?>
                    <span class="brand-subline"><?= htmlspecialchars($clubName, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </span>
        </a>
    </div>
</header>

<main class="container site-shell">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container site-footer-inner">
        <nav class="nav footer-nav">
            <a href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/"><?= htmlspecialchars($ui['nav_roster'], ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/admin/players"><?= htmlspecialchars($ui['nav_admin'], ENT_QUOTES, 'UTF-8') ?></a>
        </nav>
        <span class="muted">
            <?php if ($clubName !== ''): ?>
                <?= htmlspecialchars(($ui['club_presented_by'] ?? 'Presented by') . ' ' . $clubName, ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                <?= htmlspecialchars($ui['made_for'], ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </span>
        <?php if ($clubName !== '' && !empty($ui['club_branding_copy'])): ?>
            <span class="muted"><?= htmlspecialchars($ui['club_branding_copy'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
    </div>
</footer>
</body>
</html>
