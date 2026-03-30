<?php

$team = $config['team'];
$club = $config['club'] ?? [];
$clubName = trim((string) ($club['name'] ?? ''));
$clubLogoPath = trim((string) ($club['logo_path'] ?? ''));
$clubUrl = trim((string) ($club['url'] ?? ''));
$clubLogoUrl = $clubLogoPath !== '' ? public_asset_url($clubLogoPath, $config) : '';
$author = $author ?? '';
$paletteOptions = $paletteOptions ?? [];
$palette = $palette ?? resolve_share_palette('primary', $config, $locale);
$personalizedTitle = personalized_roster_title($t, $author);
$clubDescription = trim((string) ($t['club_branding_copy'] ?? ''));
$shapeItems = [
    $t['shape_qb'] ?? '2 Quarterbacks',
    $t['shape_rb'] ?? '4 Running Backs',
    $t['shape_wr'] ?? '6 Wide Receivers',
    $t['shape_te'] ?? '3 Tight Ends',
    $t['shape_ol'] ?? '9 Offensive Linemen',
    $t['shape_lb'] ?? '7 Linebackers',
    $t['shape_cb'] ?? '6 Cornerbacks',
    $t['shape_s'] ?? '4 Safeties',
    $t['shape_dl'] ?? '9 Defensive Linemen',
    $t['shape_st'] ?? '3 Special Teamers',
];

$simulatorConfig = [
    'players' => $simulator['players'],
    'sections' => $simulator['sections'],
    'selectedIds' => $simulator['selected_ids'],
    'rosterLimit' => $simulator['roster_limit'],
    'locale' => $locale,
    'basePath' => $config['app']['base_path'],
    'labels' => [
        'summaryShort' => $t['summary_short'],
        'selected' => $t['selected_label'],
        'remaining' => $t['remaining_label'],
        'complete' => $t['status_complete'],
        'incomplete' => $t['status_incomplete'],
        'copyDone' => $t['copy_done'],
        'shareCaption' => $t['share_caption'],
        'experienceRookie' => $t['experience_rookie'],
        'experienceYearSingular' => $t['experience_year_singular'],
        'experienceYearPlural' => $t['experience_year_plural'],
        'selectedRoster' => $t['selected_roster'],
        'selectedRosterNamed' => $t['selected_roster_named'],
    ],
    'personalization' => [
        'author' => $author,
        'scheme' => $palette['key'],
        'palettes' => $paletteOptions,
    ],
];

ob_start();
?>
<section class="sim-hero">
    <div class="sim-hero-copy">
        <div class="eyebrow"><?= htmlspecialchars(strtoupper($team['name']), ENT_QUOTES, 'UTF-8') ?></div>
        <h1><?= htmlspecialchars($t['headline'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="lead"><?= htmlspecialchars($t['subline'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($clubName !== ''): ?>
            <<?= $clubUrl !== '' ? 'a' : 'div' ?>
                class="club-note"
                <?php if ($clubUrl !== ''): ?>
                    href="<?= htmlspecialchars($clubUrl, ENT_QUOTES, 'UTF-8') ?>"
                    target="_blank"
                    rel="noreferrer"
                <?php endif; ?>
            >
                <?php if ($clubLogoUrl !== ''): ?>
                    <img class="club-note-logo" src="<?= htmlspecialchars($clubLogoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($clubName, ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>
                <div>
                    <strong><?= htmlspecialchars(($t['club_presented_by'] ?? 'Presented by') . ' ' . $clubName, ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php if ($clubDescription !== ''): ?>
                        <div class="club-note-copy"><?= htmlspecialchars($clubDescription, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
            </<?= $clubUrl !== '' ? 'a' : 'div' ?>>
        <?php endif; ?>
    </div>
    <div class="card-panel">
        <div class="club-panel-head">
            <?php if ($clubLogoUrl !== ''): ?>
                <img class="club-panel-logo" src="<?= htmlspecialchars($clubLogoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($clubName !== '' ? $clubName : $team['name'], ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <div class="club-panel-name"><?= htmlspecialchars($t['intro_title'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <p class="muted intro-lead"><?= htmlspecialchars($t['intro_body'], ENT_QUOTES, 'UTF-8') ?></p>
        <p class="muted intro-lead"><?= htmlspecialchars($t['intro_body_followup'], ENT_QUOTES, 'UTF-8') ?></p>
        <div class="intro-sections">
            <section class="intro-section intro-shape">
                <h3><?= htmlspecialchars($t['intro_shape_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="muted"><?= htmlspecialchars($t['intro_shape_body'], ENT_QUOTES, 'UTF-8') ?></p>
                <div class="intro-shape-grid">
                    <?php foreach ($shapeItems as $shapeItem): ?>
                        <div class="intro-shape-item"><?= htmlspecialchars($shapeItem, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
        <div class="locale-switcher">
            <?php foreach (($availableLocales ?? supported_locales()) as $localeCode => $localeLabel): ?>
                <a
                    class="button<?= $localeCode === $locale ? '' : ' secondary' ?>"
                    href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/?lang=<?= htmlspecialchars($localeCode, ENT_QUOTES, 'UTF-8') ?>"
                ><?= htmlspecialchars($localeLabel, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="sim-layout" data-simulator='<?= htmlspecialchars(json_encode($simulatorConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'>
    <div class="sim-main">
        <div class="sim-tabs">
            <?php foreach ($simulator['sections'] as $section): ?>
                <?php foreach ($section['groups'] as $group): ?>
                    <button
                        type="button"
                        class="sim-tab<?= ($group === $simulator['sections'][0]['groups'][0]) ? ' active' : '' ?>"
                        data-group-tab="<?= htmlspecialchars($group['key'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <span><?= htmlspecialchars($group['key'], ENT_QUOTES, 'UTF-8') ?></span>
                        <small><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></small>
                    </button>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <div class="sim-board">
            <div class="card-panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-kicker"><?= htmlspecialchars($t['available_label'], ENT_QUOTES, 'UTF-8') ?></div>
                        <h2 data-current-group-label><?= htmlspecialchars($simulator['sections'][0]['groups'][0]['label'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <div class="panel-count" data-current-group-count></div>
                </div>
                <div class="player-grid" data-available-list></div>
                <p class="empty-copy" data-available-empty hidden><?= htmlspecialchars($t['empty_state'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="card-panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-kicker"><?= htmlspecialchars($t['tab_selected'], ENT_QUOTES, 'UTF-8') ?></div>
                        <h2 data-selected-group-label><?= htmlspecialchars($simulator['sections'][0]['groups'][0]['label'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <div class="panel-count" data-selected-group-count></div>
                </div>
                <div class="player-grid selected-grid" data-selected-list></div>
            </div>
        </div>

        <div class="card-panel review-panel personalized-panel" data-personalized-panel style="<?= htmlspecialchars(share_palette_style($palette), ENT_QUOTES, 'UTF-8') ?>">
            <div class="personalize-grid">
                <label class="personalize-field">
                    <span class="personalize-label"><?= htmlspecialchars($t['personalize_name_label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <input
                        type="text"
                        value="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="40"
                        placeholder="<?= htmlspecialchars($t['personalize_name_placeholder'], ENT_QUOTES, 'UTF-8') ?>"
                        data-author-input
                    >
                </label>
                <div class="personalize-field">
                    <span class="personalize-label"><?= htmlspecialchars($t['personalize_palette_label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="palette-picker">
                        <?php foreach ($paletteOptions as $paletteOption): ?>
                            <?php $swatch = $paletteOption['colors']['primary'] ?? '#0b2545'; ?>
                            <button
                                type="button"
                                class="palette-button<?= $paletteOption['key'] === $palette['key'] ? ' active' : '' ?>"
                                data-palette-scheme="<?= htmlspecialchars($paletteOption['key'], ENT_QUOTES, 'UTF-8') ?>"
                                title="<?= htmlspecialchars($paletteOption['label'], ENT_QUOTES, 'UTF-8') ?>"
                                aria-label="<?= htmlspecialchars($paletteOption['label'], ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <span class="palette-swatch" style="background: <?= htmlspecialchars($swatch, ENT_QUOTES, 'UTF-8') ?>"></span>
                                <span><?= htmlspecialchars($paletteOption['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="panel-head">
                <div>
                    <div class="panel-kicker"><?= htmlspecialchars($t['review_title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <h2 data-personalized-title><?= htmlspecialchars($personalizedTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <div class="review-head-meta">
                    <div class="panel-count" data-summary-total><?= (int) $simulator['selected_count'] ?>/<?= (int) $simulator['roster_limit'] ?></div>
                    <div class="status-chip" data-roster-status><?= htmlspecialchars($simulator['complete'] ? $t['status_complete'] : $t['status_incomplete'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>

            <p class="muted"><?= htmlspecialchars($t['personalize_body'], ENT_QUOTES, 'UTF-8') ?></p>

            <div class="metric-grid">
                <div class="metric-box">
                    <span><?= htmlspecialchars($t['selected_label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <strong data-metric-selected><?= (int) $simulator['selected_count'] ?></strong>
                </div>
                <div class="metric-box">
                    <span><?= htmlspecialchars($t['remaining_label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <strong data-metric-remaining><?= (int) $simulator['remaining'] ?></strong>
                </div>
            </div>

            <div class="review-grid">
                <?php foreach ($simulator['sections'] as $section): ?>
                    <article class="review-card">
                        <h3><?= htmlspecialchars($section['label'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php foreach ($section['groups'] as $group): ?>
                            <div class="summary-line">
                                <span><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                <strong data-sidebar-count="<?= htmlspecialchars($group['key'], ENT_QUOTES, 'UTF-8') ?>"><?= (int) $group['count_selected'] ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="share-box">
                <h3><?= htmlspecialchars($t['share_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="muted"><?= htmlspecialchars($t['share_body'], ENT_QUOTES, 'UTF-8') ?></p>
                <input type="text" readonly data-share-url value="<?= htmlspecialchars($shareUrl . '&roster=' . implode(',', $simulator['selected_ids']), ENT_QUOTES, 'UTF-8') ?>">
                <div class="share-actions">
                    <button type="button" data-copy-link><?= htmlspecialchars($t['copy_link'], ENT_QUOTES, 'UTF-8') ?></button>
                    <a class="button secondary" data-share-page href="<?= htmlspecialchars($shareUrl . '&roster=' . implode(',', $simulator['selected_ids']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t['open_share'], ENT_QUOTES, 'UTF-8') ?></a>
                    <a class="button secondary" data-share-card href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/share/card.svg?lang=<?= htmlspecialchars($locale, ENT_QUOTES, 'UTF-8') ?>&roster=<?= htmlspecialchars(implode(',', $simulator['selected_ids']), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noreferrer"><?= htmlspecialchars($t['download_card'], ENT_QUOTES, 'UTF-8') ?></a>
                    <a class="button secondary" data-whatsapp-link href="#"><?= htmlspecialchars($t['share_whatsapp'], ENT_QUOTES, 'UTF-8') ?></a>
                    <button type="button" class="secondary" data-native-share><?= htmlspecialchars($t['share_native'], ENT_QUOTES, 'UTF-8') ?></button>
                </div>
                <p class="hint" data-copy-feedback><?= htmlspecialchars($t['review_hint'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>
</section>

<script src="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/assets/simulator.js" defer></script>
<?php
$content = (string) ob_get_clean();

echo App\Core\View::make('layouts/app', [
    'config' => $config,
    'title' => $team['name'] . ' ' . $t['headline'],
    'lang' => $locale,
    'content' => $content,
    'theme' => $config['team']['colors'],
]);
