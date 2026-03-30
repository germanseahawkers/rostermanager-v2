<?php

$team = $config['team'];
$club = $config['club'] ?? [];
$clubName = trim((string) ($club['name'] ?? ''));
$clubLogoPath = trim((string) ($club['logo_path'] ?? ''));
$clubUrl = trim((string) ($club['url'] ?? ''));
$clubLogoUrl = $clubLogoPath !== '' ? public_asset_url($clubLogoPath, $config) : '';
$author = $author ?? '';
$palette = $palette ?? resolve_share_palette('primary', $config, $locale);
$personalizedTitle = personalized_roster_title($t, $author);
$clubDescription = trim((string) ($t['club_branding_copy'] ?? ''));
$shareUrl = trim((string) ($shareUrl ?? ''));
$shareCardUrl = trim((string) ($shareCardUrl ?? ''));
$simulatorUrl = trim((string) ($simulatorUrl ?? ''));
$shareDescriptionTemplate = trim((string) ($author !== ''
    ? ($t['share_meta_description_named'] ?? '')
    : ($t['share_meta_description_default'] ?? '')));
$shareDescription = $shareDescriptionTemplate;

if ($author !== '' && $shareDescriptionTemplate !== '') {
    $shareDescription = trim(sprintf($shareDescriptionTemplate, $author));
}

$formatExperience = static function (mixed $value) use ($t): string {
    $normalized = is_string($value) || is_numeric($value) ? trim((string) $value) : '';

    if ($normalized === '') {
        return '';
    }

    if (!ctype_digit($normalized)) {
        return $normalized;
    }

    $years = (int) $normalized;

    if ($years === 0) {
        return (string) ($t['experience_rookie'] ?? 'Rookie');
    }

    if ($years === 1) {
        return '1 ' . ($t['experience_year_singular'] ?? 'year');
    }

    return $years . ' ' . ($t['experience_year_plural'] ?? 'years');
};

$playerImageUrl = static function (string $path) use ($config): string {
    if (preg_match('/^https?:\/\//i', $path) === 1) {
        return $path;
    }

    return public_asset_url($path, $config);
};

ob_start();
?>
<section class="sim-hero share-hero-full personalized-panel" style="<?= htmlspecialchars(share_palette_style($palette), ENT_QUOTES, 'UTF-8') ?>">
    <div class="sim-hero-copy share-hero-copy">
        <div class="share-hero-topline">
            <div class="eyebrow"><?= htmlspecialchars(strtoupper($team['name']), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <h1><?= htmlspecialchars($personalizedTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="lead"><?= htmlspecialchars($t['review_body'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($clubName !== ''): ?>
            <<?= $clubUrl !== '' ? 'a' : 'div' ?>
                class="club-note share-club-note"
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

        <div class="share-hero-tools">
            <div class="metric-grid share-metric-grid">
                <div class="metric-box">
                    <span><?= htmlspecialchars($t['selected_label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <strong><?= (int) $simulator['selected_count'] ?></strong>
                </div>
                <div class="metric-box">
                    <span><?= htmlspecialchars($t['remaining_label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <strong><?= (int) $simulator['remaining'] ?></strong>
                </div>
            </div>
            <div class="share-actions share-hero-actions">
                <button type="button" class="button secondary" data-copy-link><?= htmlspecialchars($t['copy_link'], ENT_QUOTES, 'UTF-8') ?></button>
                <a class="button secondary" href="https://wa.me/?text=<?= htmlspecialchars(rawurlencode(($t['share_caption'] ?? '') . ': ' . $shareUrl), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noreferrer"><?= htmlspecialchars($t['share_whatsapp'], ENT_QUOTES, 'UTF-8') ?></a>
                <button type="button" class="button secondary" data-native-share data-share-url="<?= htmlspecialchars($shareUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t['share_native'], ENT_QUOTES, 'UTF-8') ?></button>
                <a class="button" href="<?= htmlspecialchars($simulatorUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t['load_this_roster'], ENT_QUOTES, 'UTF-8') ?></a>
            </div>
            <p class="hint" data-copy-feedback><?= htmlspecialchars($t['review_hint'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</section>

<section class="review-grid share-review-grid personalized-panel" style="<?= htmlspecialchars(share_palette_style($palette), ENT_QUOTES, 'UTF-8') ?>">
    <?php foreach ($simulator['sections'] as $section): ?>
        <article class="review-card">
            <h2><?= htmlspecialchars($section['label'], ENT_QUOTES, 'UTF-8') ?></h2>
            <?php foreach ($section['groups'] as $group): ?>
                <details class="share-group">
                    <summary class="share-group-summary">
                        <h3><?= htmlspecialchars($group['label'] . ' (' . (int) $group['count_selected'] . ')', ENT_QUOTES, 'UTF-8') ?></h3>
                    </summary>
                    <?php if ($group['selected'] === []): ?>
                        <p class="muted">—</p>
                    <?php else: ?>
                        <div class="player-grid share-player-grid">
                            <?php foreach ($group['selected'] as $player): ?>
                                <?php
                                $experience = $formatExperience($player['experience'] ?? '');
                                $heightCm = metric_height_cm($player);
                                $weightKg = metric_weight_kg($player);
                                $measurements = [];

                                if ($heightCm !== null && $heightCm > 0) {
                                    $measurements[] = $heightCm . ' cm';
                                }

                                if ($weightKg !== null && $weightKg > 0) {
                                    $measurements[] = $weightKg . ' kg';
                                }
                                ?>
                                <article class="player-card share-player-card">
                                    <div class="player-avatar">
                                        <?php if (!empty($player['image'])): ?>
                                            <img class="player-photo" src="<?= htmlspecialchars($playerImageUrl((string) $player['image']), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?php else: ?>
                                            <?= htmlspecialchars(substr((string) ($player['position'] ?? '?'), 0, 3), ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="player-name"><?= htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="player-meta">
                                            <?= htmlspecialchars((string) ($player['group_label'] ?? $group['label']), ENT_QUOTES, 'UTF-8') ?>
                                            <?php if ($experience !== ''): ?>
                                                · <?= htmlspecialchars($experience, ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($measurements !== []): ?>
                                            <div class="hint"><?= htmlspecialchars(implode(' · ', $measurements), ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </details>
            <?php endforeach; ?>
        </article>
    <?php endforeach; ?>
</section>
<script>
(() => {
  const copyButton = document.querySelector("[data-copy-link]");
  const nativeShareButton = document.querySelector("[data-native-share]");
  const feedback = document.querySelector("[data-copy-feedback]");
  const shareUrl = nativeShareButton?.getAttribute("data-share-url") || "";
  const shareText = <?= json_encode($t['share_caption'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const copiedLabel = <?= json_encode($t['copy_done'] ?? 'Link copied', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  copyButton?.addEventListener("click", async () => {
    if (!shareUrl) return;

    try {
      await navigator.clipboard.writeText(shareUrl);
      if (feedback) feedback.textContent = copiedLabel;
    } catch (error) {
      // Ignore clipboard fallback errors on the share page.
    }
  });

  nativeShareButton?.addEventListener("click", async () => {
    if (!navigator.share || !shareUrl) return;

    try {
      await navigator.share({
        title: document.title,
        text: shareText,
        url: shareUrl,
      });
    } catch (error) {
      // Ignore cancelled share actions.
    }
  });
})();
</script>
<?php
$content = (string) ob_get_clean();

echo App\Core\View::make('layouts/app', [
    'config' => $config,
    'title' => $team['name'] . ' ' . $t['share_page_title'],
    'metaTitle' => $personalizedTitle,
    'metaDescription' => $shareDescription,
    'canonicalUrl' => $shareUrl,
    'ogImageUrl' => $shareCardUrl,
    'ogType' => 'article',
    'lang' => $locale,
    'content' => $content,
    'theme' => $config['team']['colors'],
]);
