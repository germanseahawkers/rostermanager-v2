<?php

declare(strict_types=1);

use App\Core\Request;

function config_value(array $config, string $key, mixed $default = null): mixed
{
    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function csrf_token(): string
{
    $token = \App\Core\Session::get('_csrf_token');

    if (!is_string($token) || $token === '') {
        $token = bin2hex(random_bytes(32));
        \App\Core\Session::put('_csrf_token', $token);
    }

    return $token;
}

function csrf_is_valid(string $token): bool
{
    $sessionToken = \App\Core\Session::get('_csrf_token');

    return is_string($sessionToken) && $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function translations(string $locale): array
{
    $messages = [
        'de' => [
            'nav_roster' => 'Roster',
            'nav_admin' => 'Admin',
            'headline' => 'Aktueller Team-Roster',
            'subline' => 'Schlanke öffentliche Übersicht mit Positionsgruppen, vorbereitet für Deutsch und Englisch.',
            'filter_label' => 'Position filtern',
            'filter_all' => 'Alle Positionen',
            'empty_state' => 'Für diese Auswahl sind noch keine Spieler vorhanden.',
            'experience' => 'Erfahrung',
            'height' => 'Größe',
            'weight' => 'Gewicht',
            'roster_note' => 'MVP-Hinweis: Veröffentlichen oder Export kann später als PDF/CSV ergänzt werden.',
        ],
        'en' => [
            'nav_roster' => 'Roster',
            'nav_admin' => 'Admin',
            'headline' => 'Current team roster',
            'subline' => 'Lean public roster view with grouped positions and built-in German/English support.',
            'filter_label' => 'Filter by position',
            'filter_all' => 'All positions',
            'empty_state' => 'No players available for the current selection yet.',
            'experience' => 'Experience',
            'height' => 'Height',
            'weight' => 'Weight',
            'roster_note' => 'MVP note: publishing or export can be added later as PDF/CSV with low effort.',
        ],
    ];

    return $messages[$locale] ?? $messages['de'];
}

function normalizePlayerPayload(Request $request): array
{
    return normalizePlayerArray([
        'name' => $request->input('name', ''),
        'position' => $request->input('position', ''),
        'abbr' => $request->input('abbr', ''),
        'experience' => $request->input('experience', ''),
        'weight' => $request->input('weight', ''),
        'height' => $request->input('height', ''),
        'image' => $request->input('image', ''),
        'ordering' => $request->input('ordering', '0'),
    ]);
}

function normalizePlayerArray(array $input): array
{
    return [
        'name' => trim((string) ($input['name'] ?? '')),
        'position' => strtoupper(trim((string) ($input['position'] ?? ''))),
        'abbr' => strtoupper(trim((string) ($input['abbr'] ?? ''))),
        'experience' => trim((string) ($input['experience'] ?? '')),
        'weight' => trim((string) ($input['weight'] ?? '')),
        'height' => trim((string) ($input['height'] ?? '')),
        'image' => trim((string) ($input['image'] ?? '')),
        'ordering' => (int) ($input['ordering'] ?? 0),
    ];
}

function emptyPlayer(): array
{
    return [
        'id' => null,
        'name' => '',
        'position' => '',
        'abbr' => '',
        'experience' => '',
        'weight' => '',
        'height' => '',
        'image' => '',
        'ordering' => 0,
    ];
}
