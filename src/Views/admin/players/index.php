<?php

$isEditing = !empty($player['id']);
$formAction = $isEditing ? '/admin/players/update' : '/admin/players';

ob_start();
?>
<section class="hero">
    <div class="toolbar">
        <div>
            <h1>Admin: Players</h1>
            <p class="muted">MVP scope: one protected admin area, player CRUD, CSV import and a clean path for later expansion.</p>
        </div>
        <form class="inline" method="post" action="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/logout">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button class="secondary" type="submit">Log out</button>
        </form>
    </div>
</section>

<?php if (!empty($success)): ?>
    <div class="notice success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<section class="split">
    <div class="stack">
        <div class="table-wrap card">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Experience</th>
                    <th>Ordering</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($players as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($item['position'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($item['experience'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $item['ordering'] ?></td>
                        <td>
                            <a class="button secondary" href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/admin/players/edit?id=<?= (int) $item['id'] ?>">Edit</a>
                            <form class="inline" method="post" action="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/admin/players/delete" onsubmit="return confirm('Delete this player?');">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button class="danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="stack">
        <form class="form-panel stack" method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($config['app']['base_path'] . $formAction, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <h2><?= $isEditing ? 'Edit player' : 'New player' ?></h2>
            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= (int) $player['id'] ?>">
            <?php endif; ?>

            <div class="field-grid">
                <label>Name
                    <input type="text" name="name" value="<?= htmlspecialchars((string) $player['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                </label>
                <label>Position
                    <input type="text" name="position" value="<?= htmlspecialchars((string) $player['position'], ENT_QUOTES, 'UTF-8') ?>" required>
                </label>
                <label>Experience
                    <input type="text" name="experience" value="<?= htmlspecialchars((string) $player['experience'], ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label>Weight (kg)
                    <input type="number" min="1" name="weight_kg" value="<?= htmlspecialchars((string) (metric_weight_kg($player) ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label>Height (cm)
                    <input type="number" min="1" name="height_cm" value="<?= htmlspecialchars((string) (metric_height_cm($player) ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label>Image URL / Path
                    <input type="text" name="image" value="<?= htmlspecialchars((string) $player['image'], ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label>Player image upload
                    <input type="file" name="image_upload" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                </label>
                <label>Ordering
                    <input type="number" name="ordering" value="<?= (int) $player['ordering'] ?>">
                </label>
            </div>

            <p class="hint">Ordering only controls manual display order in admin and simulator lists. Using gaps like 10, 20, 30 makes later reordering easier.</p>

            <?php if (!empty($player['image'])): ?>
                <div class="stack">
                    <span class="hint">Current player image</span>
                    <img
                        src="<?= htmlspecialchars(public_asset_url((string) $player['image'], $config), ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars((string) $player['name'], ENT_QUOTES, 'UTF-8') ?>"
                        style="width: 112px; height: 112px; object-fit: cover; border-radius: 18px; border: 1px solid rgba(181, 197, 214, 0.8);"
                    >
                </div>
            <?php endif; ?>

            <div class="actions">
                <button type="submit"><?= $isEditing ? 'Save changes' : 'Create player' ?></button>
                <?php if ($isEditing): ?>
                    <a class="button secondary" href="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/admin/players">Cancel</a>
                <?php endif; ?>
            </div>
        </form>

        <form class="form-panel stack" method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/admin/players/import">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <h2>CSV import</h2>
            <p class="hint">Expected header: <code>id,name,position,experience,weight_kg,height_cm,image,ordering</code>. The <code>id</code> column is optional.</p>
            <p class="hint">Without IDs, every row is imported as a new player. With IDs in every row, the import becomes a sync: existing IDs are updated, missing IDs are deleted, and ordering is preserved for updated players.</p>
            <label>
                CSV file
                <input type="file" name="csv" accept=".csv,text/csv" required>
            </label>
            <label>
                Images ZIP (optional)
                <input type="file" name="images_zip" accept=".zip,application/zip">
            </label>
            <button type="submit">Import players</button>
        </form>

        <form class="form-panel stack" method="post" action="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/admin/players/import/espn">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <h2>ESPN import</h2>
            <p class="hint">Fetch the current team roster directly from ESPN and sync it to the database by ESPN player ID. Existing IDs are updated, missing IDs are removed, and manual ordering is preserved for players already in the database.</p>
            <label>
                ESPN team ID
                <input type="number" min="1" name="team_id" value="<?= htmlspecialchars((string) ($config['team']['espn_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </label>
            <label style="display: flex; align-items: center; gap: 0.6rem; font-weight: 600;">
                <input type="checkbox" name="download_images" value="1">
                <span>Download player images and store them locally</span>
            </label>
            <button type="submit">Import from ESPN</button>
        </form>
    </div>
</section>
<?php
$content = (string) ob_get_clean();

echo App\Core\View::make('layouts/app', [
    'config' => $config,
    'title' => 'Admin: Players',
    'content' => $content,
]);
