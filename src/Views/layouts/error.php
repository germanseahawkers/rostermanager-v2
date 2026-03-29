<?php

$content = sprintf(
    '<section class="hero"><h1>%s</h1><p>%s</p></section>',
    htmlspecialchars($title ?? 'Error', ENT_QUOTES, 'UTF-8'),
    htmlspecialchars($message ?? 'Unknown error.', ENT_QUOTES, 'UTF-8')
);

echo App\Core\View::make('layouts/app', [
    'config' => $config ?? ['app' => ['name' => 'RosterManager v2', 'base_path' => '']],
    'title' => $title ?? 'Error',
    'lang' => 'en',
    'content' => $content,
]);
