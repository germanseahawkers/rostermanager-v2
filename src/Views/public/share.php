<?php

$team = $config['team'];
$club = $config['club'] ?? [];
$clubName = trim((string) ($club['name'] ?? ''));
$clubTagline = trim((string) ($club['tagline'] ?? ''));
$clubLogoPath = trim((string) ($club['logo_path'] ?? ''));
$clubLogoUrl = $clubLogoPath !== '' ? public_asset_url($clubLogoPath, $config) : '';

ob_start();
?>
<section class="sim-hero compact">
    <div class="sim-hero-copy">
        <div class="eyebrow"><?= htmlspecialchars(strtoupper($team['city']), ENT_QUOTES, 'UTF-8') ?></div>
        <h1><?= htmlspecialchars($team['name'] . ' ' . $t['share_page_title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="lead"><?= htmlspecialchars($t['review_body'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($clubName !== ''): ?>
            <div class="club-note">
                <?php if ($clubLogoUrl !== ''): ?>
                    <img class="club-note-logo" src="<?= htmlspecialchars($clubLogoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($clubName, ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>
                <div>
                    <strong><?= htmlspecialchars(($t['club_presented_by'] ?? 'Presented by') . ' ' . $clubName, ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php if ($clubTagline !== ''): ?>
                        <div class="club-note-copy"><?= htmlspecialchars($clubTagline, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-panel">
        <div class="metric-grid">
            <div class="metric-box">
                <span><?= htmlspecialchars($t['selected_label'], ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= (int) $simulator['selected_count'] ?></strong>
            </div>
            <div class="metric-box">
                <span><?= htmlspecialchars($t['remaining_label'], ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= (int) $simulator['remaining'] ?></strong>
            </div>
        </div>
        <div class="share-actions">
            <a class="button" href="<?= htmlspecialchars($simulatorUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t['load_this_roster'], ENT_QUOTES, 'UTF-8') ?></a>
            <a class="button secondary" href="<?= htmlspecialchars($shareCardUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noreferrer"><?= htmlspecialchars($t['download_card'], ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="review-grid share-review-grid">
    <?php foreach ($simulator['sections'] as $section): ?>
        <article class="review-card">
            <h2><?= htmlspecialchars($section['label'], ENT_QUOTES, 'UTF-8') ?></h2>
            <?php foreach ($section['groups'] as $group): ?>
                <div class="share-group">
                    <h3><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?> <span><?= (int) $group['count_selected'] ?></span></h3>
                    <?php if ($group['selected'] === []): ?>
                        <p class="muted">—</p>
                    <?php else: ?>
                        <ul class="name-list">
                            <?php foreach ($group['selected'] as $player): ?>
                                <li><?= htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php
$content = (string) ob_get_clean();

echo App\Core\View::make('layouts/app', [
    'config' => $config,
    'title' => $team['name'] . ' ' . $t['share_page_title'],
    'lang' => $locale,
    'content' => $content,
    'theme' => $config['team']['colors'],
]);
