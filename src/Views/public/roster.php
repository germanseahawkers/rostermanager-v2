<?php

$team = $config['team'];

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
    ],
];

ob_start();
?>
<section class="sim-hero">
    <div class="sim-hero-copy">
        <div class="eyebrow"><?= htmlspecialchars(strtoupper($team['city']), ENT_QUOTES, 'UTF-8') ?></div>
        <h1><?= htmlspecialchars($team['name'] . ' ' . $t['headline'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="lead"><?= htmlspecialchars($t['subline'], ENT_QUOTES, 'UTF-8') ?></p>
        <div class="sim-pills">
            <span class="pill"><?= htmlspecialchars($t['intro_title'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="pill"><?= htmlspecialchars($t['share_title'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="pill">Open Source Ready</span>
        </div>
    </div>
    <div class="card-panel">
        <div class="panel-kicker"><?= htmlspecialchars($t['intro_title'], ENT_QUOTES, 'UTF-8') ?></div>
        <h2><?= htmlspecialchars($team['nickname'] . ' Cutdown', ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="muted"><?= htmlspecialchars($t['intro_body'], ENT_QUOTES, 'UTF-8') ?></p>
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

        <div class="card-panel review-panel">
            <div class="panel-head">
                <div>
                    <div class="panel-kicker"><?= htmlspecialchars($t['review_title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <h2><?= htmlspecialchars($t['selected_roster'], ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <div class="status-chip" data-roster-status><?= htmlspecialchars($simulator['complete'] ? $t['status_complete'] : $t['status_incomplete'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <p class="muted"><?= htmlspecialchars($t['review_body'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="review-grid">
                <?php foreach ($simulator['sections'] as $section): ?>
                    <article class="review-card">
                        <h3><?= htmlspecialchars($section['label'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php foreach ($section['groups'] as $group): ?>
                            <div class="review-row">
                                <span><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                <strong data-review-count="<?= htmlspecialchars($group['key'], ENT_QUOTES, 'UTF-8') ?>"><?= (int) $group['count_selected'] ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <aside class="sim-sidebar">
        <div class="card-panel sticky-panel">
            <div class="sidebar-title">
                <div>
                    <div class="panel-kicker"><?= htmlspecialchars($t['selected_roster'], ENT_QUOTES, 'UTF-8') ?></div>
                    <h2 data-summary-total><?= (int) $simulator['selected_count'] ?>/<?= (int) $simulator['roster_limit'] ?></h2>
                </div>
            </div>

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

            <?php foreach ($simulator['sections'] as $section): ?>
                <div class="summary-block">
                    <h3><?= htmlspecialchars($section['label'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php foreach ($section['groups'] as $group): ?>
                        <div class="summary-line">
                            <span><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            <strong data-sidebar-count="<?= htmlspecialchars($group['key'], ENT_QUOTES, 'UTF-8') ?>"><?= (int) $group['count_selected'] ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

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
    </aside>
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
