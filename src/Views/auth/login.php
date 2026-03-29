<?php

ob_start();
?>
<section class="hero" style="max-width: 520px; margin-inline: auto;">
    <h1>Admin login</h1>
    <p class="muted">Minimal authentication for the MVP. Credentials are configured server-side.</p>

    <?php if (!empty($error)): ?>
        <div class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form class="stack form-panel" method="post" action="<?= htmlspecialchars($config['app']['base_path'], ENT_QUOTES, 'UTF-8') ?>/login">
        <input type="hidden" name="_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <label>
            Username
            <input type="text" name="username" required>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <button type="submit">Log in</button>
    </form>
</section>
<?php
$content = (string) ob_get_clean();

echo App\Core\View::make('layouts/app', [
    'config' => $config,
    'title' => 'Admin login',
    'content' => $content,
]);
