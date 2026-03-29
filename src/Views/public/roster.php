<?php

$positionOptions = array_keys($playersByPosition);

ob_start();
?>
<section class="hero">
    <div class="toolbar">
        <div>
            <h1><?= htmlspecialchars($t['headline'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="muted"><?= htmlspecialchars($t['subline'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="locale-switcher">
            <a class="button secondary" href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/?lang=de">DE</a>
            <a class="button secondary" href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/?lang=en">EN</a>
        </div>
    </div>

    <form method="get" class="toolbar">
        <input type="hidden" name="lang" value="<?= htmlspecialchars($locale, ENT_QUOTES, 'UTF-8') ?>">
        <label style="min-width: 240px;">
            <?= htmlspecialchars($t['filter_label'], ENT_QUOTES, 'UTF-8') ?>
            <select name="position">
                <option value=""><?= htmlspecialchars($t['filter_all'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php foreach ($positionOptions as $position): ?>
                    <option value="<?= htmlspecialchars($position, ENT_QUOTES, 'UTF-8') ?>" <?= $positionFilter === $position ? 'selected' : '' ?>>
                        <?= htmlspecialchars($position, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit"><?= htmlspecialchars($t['filter_label'], ENT_QUOTES, 'UTF-8') ?></button>
    </form>
</section>

<?php if (empty($playersByPosition)): ?>
    <div class="notice"><?= htmlspecialchars($t['empty_state'], ENT_QUOTES, 'UTF-8') ?></div>
<?php else: ?>
    <section class="grid roster-grid">
        <?php foreach ($playersByPosition as $position => $players): ?>
            <article class="card">
                <h2 class="section-title"><?= htmlspecialchars($position, ENT_QUOTES, 'UTF-8') ?></h2>
                <ul class="player-list">
                    <?php foreach ($players as $player): ?>
                        <li class="player-row">
                            <div>
                                <strong><?= htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <div class="player-meta">
                                    <?= htmlspecialchars($player['abbr'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($player['experience'] !== ''): ?>
                                        · <?= htmlspecialchars($t['experience'], ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars($player['experience'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="player-meta">
                                <?php if ($player['height'] !== ''): ?>
                                    <div><?= htmlspecialchars($t['height'], ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars($player['height'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                                <?php if ($player['weight'] !== ''): ?>
                                    <div><?= htmlspecialchars($t['weight'], ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars($player['weight'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<div class="notice">
    <?= htmlspecialchars($t['roster_note'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php
$content = (string) ob_get_clean();

echo App\Core\View::make('layouts/app', [
    'config' => $config,
    'title' => $t['headline'],
    'lang' => $locale,
    'content' => $content,
]);
