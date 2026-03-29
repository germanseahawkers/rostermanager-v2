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
            'nav_roster' => 'Simulator',
            'nav_admin' => 'Admin',
            'headline' => '53-Man Roster Simulator',
            'subline' => 'Baue aus dem 90-Mann-Kader deinen eigenen finalen Cutdown-Roster.',
            'intro_title' => 'Von 90 auf 53',
            'intro_body' => 'Wähle Spieler aus dem vollständigen Camp-Kader aus, beobachte live deinen Cutdown-Zähler und teile dein Ergebnis mit deiner Fan-Community.',
            'tab_available' => 'Verfügbare Spieler',
            'tab_selected' => 'Dein Roster',
            'share_title' => 'Teilen und diskutieren',
            'share_body' => 'Dein Ergebnis bekommt eine eigene URL und ein automatisch gerendertes Share-Visual.',
            'review_title' => 'Review',
            'review_body' => 'Prüfe deinen finalen 53er-Kader und teile ihn in WhatsApp-Gruppen, auf Social oder direkt im Fanclub.',
            'copy_link' => 'Link kopieren',
            'open_share' => 'Share-Seite öffnen',
            'download_card' => 'Share-Grafik öffnen',
            'share_whatsapp' => 'Per WhatsApp teilen',
            'share_native' => 'Teilen',
            'available_label' => 'Verfügbare Spieler',
            'selected_roster' => 'Dein 53er-Roster',
            'empty_state' => 'Für diese Positionsgruppe gibt es aktuell keine Spieler.',
            'experience' => 'Erfahrung',
            'height' => 'Größe',
            'weight' => 'Gewicht',
            'summary_total' => 'Gesamt',
            'summary_short' => 'von',
            'selected_label' => 'Ausgewählt',
            'remaining_label' => 'Verbleibend',
            'status_complete' => 'Roster komplett',
            'status_incomplete' => 'Noch nicht vollständig',
            'built_with' => 'Open-source-fähiger NFL roster cut simulator',
            'made_for' => 'Team-Branding und Positionslogik zentral konfigurierbar',
            'share_page_title' => 'Geteilter Roster',
            'load_this_roster' => 'Diesen Roster im Simulator öffnen',
            'share_caption' => 'Mein 53-Man-Roster für den Cutdown Day',
            'copy_done' => 'Link kopiert',
            'review_hint' => 'Tipp: Du kannst die Auswahl jederzeit per URL weitergeben.',
        ],
        'en' => [
            'nav_roster' => 'Simulator',
            'nav_admin' => 'Admin',
            'headline' => '53-Man Roster Simulator',
            'subline' => 'Build your own final cutdown roster from the full 90-man camp squad.',
            'intro_title' => 'From 90 to 53',
            'intro_body' => 'Pick players from the full camp roster, track your live cutdown count and share the result with your fan community.',
            'tab_available' => 'Available players',
            'tab_selected' => 'Your roster',
            'share_title' => 'Share and debate',
            'share_body' => 'Every result gets its own URL plus an automatically rendered share visual.',
            'review_title' => 'Review',
            'review_body' => 'Check your final 53-man roster and share it in WhatsApp groups, on social media or directly with your fan club.',
            'copy_link' => 'Copy link',
            'open_share' => 'Open share page',
            'download_card' => 'Open share graphic',
            'share_whatsapp' => 'Share on WhatsApp',
            'share_native' => 'Share',
            'available_label' => 'Available players',
            'selected_roster' => 'Your 53-man roster',
            'empty_state' => 'No players are available for this position group yet.',
            'experience' => 'Experience',
            'height' => 'Height',
            'weight' => 'Weight',
            'summary_total' => 'Total',
            'summary_short' => 'of',
            'selected_label' => 'Selected',
            'remaining_label' => 'Remaining',
            'status_complete' => 'Roster complete',
            'status_incomplete' => 'Not complete yet',
            'built_with' => 'Open-source-ready NFL roster cut simulator',
            'made_for' => 'Team branding and position logic are centrally configurable',
            'share_page_title' => 'Shared roster',
            'load_this_roster' => 'Open this roster in the simulator',
            'share_caption' => 'My 53-man roster for cutdown day',
            'copy_done' => 'Link copied',
            'review_hint' => 'Tip: you can share the current selection any time via URL.',
        ],
    ];

    return $messages[$locale] ?? $messages['de'];
}

function parse_roster_selection(string $rosterValue): array
{
    if (trim($rosterValue) === '') {
        return [];
    }

    $ids = array_map('trim', explode(',', $rosterValue));
    $ids = array_filter($ids, static fn (string $id): bool => ctype_digit($id));

    return array_values(array_unique(array_map('intval', $ids)));
}

function simulator_group_map(array $groups, string $locale): array
{
    $map = [];

    foreach ($groups as $group) {
        $labelKey = $locale === 'de' ? 'label_de' : 'label_en';
        $map[$group['key']] = [
            'key' => $group['key'],
            'section' => $group['section'],
            'label' => $group[$labelKey],
            'aliases' => $group['aliases'],
        ];
    }

    return $map;
}

function simulator_group_key(string $position, array $groups): string
{
    $normalizedPosition = strtoupper(trim($position));

    foreach ($groups as $group) {
        if (in_array($normalizedPosition, $group['aliases'], true)) {
            return $group['key'];
        }
    }

    return $normalizedPosition;
}

function build_simulator_payload(array $players, array $groups, string $locale, array $selectedIds, int $rosterLimit): array
{
    $groupMap = simulator_group_map($groups, $locale);
    $playersByGroup = [];
    $selectedLookup = array_fill_keys(array_map('intval', $selectedIds), true);
    $selectedPlayers = [];

    foreach ($groupMap as $groupKey => $group) {
        $playersByGroup[$groupKey] = [
            'key' => $groupKey,
            'label' => $group['label'],
            'section' => $group['section'],
            'players' => [],
            'selected' => [],
        ];
    }

    foreach ($players as $player) {
        $groupKey = simulator_group_key((string) $player['position'], $groups);

        if (!isset($playersByGroup[$groupKey])) {
            $playersByGroup[$groupKey] = [
                'key' => $groupKey,
                'label' => $groupKey,
                'section' => 'offense',
                'players' => [],
                'selected' => [],
            ];
        }

        $player['group_key'] = $groupKey;
        $player['group_label'] = $playersByGroup[$groupKey]['label'];
        $playersByGroup[$groupKey]['players'][] = $player;

        if (isset($selectedLookup[(int) $player['id']])) {
            $playersByGroup[$groupKey]['selected'][] = $player;
            $selectedPlayers[] = $player;
        }
    }

    $sections = [
        'offense' => ['key' => 'offense', 'label' => $locale === 'de' ? 'Offense' : 'Offense', 'groups' => []],
        'defense' => ['key' => 'defense', 'label' => $locale === 'de' ? 'Defense' : 'Defense', 'groups' => []],
        'special_teams' => ['key' => 'special_teams', 'label' => 'Special Teams', 'groups' => []],
    ];

    foreach ($playersByGroup as $groupKey => $group) {
        $sections[$group['section']]['groups'][] = [
            'key' => $groupKey,
            'label' => $group['label'],
            'players' => array_values($group['players']),
            'selected' => array_values($group['selected']),
            'available' => array_values(array_filter(
                $group['players'],
                static fn (array $player): bool => !isset($selectedLookup[(int) $player['id']])
            )),
            'count_selected' => count($group['selected']),
            'count_total' => count($group['players']),
        ];
    }

    $validSelectedIds = array_values(array_map(
        static fn (array $player): int => (int) $player['id'],
        $selectedPlayers
    ));

    return [
        'sections' => array_values($sections),
        'players' => array_values($players),
        'selected_ids' => $validSelectedIds,
        'selected_count' => count($selectedPlayers),
        'roster_limit' => $rosterLimit,
        'remaining' => max(0, $rosterLimit - count($selectedPlayers)),
        'complete' => count($selectedPlayers) === $rosterLimit,
    ];
}

function render_share_card_svg(array $simulator, array $config, string $locale): string
{
    $team = $config['team'];
    $colors = $team['colors'];
    $title = svg_escape($team['name'] . ' 53-Man');
    $subtitle = svg_escape(($locale === 'de' ? 'Mein Cutdown-Roster' : 'My cutdown roster') . ' · ' . count($simulator['selected_ids']) . '/' . $simulator['roster_limit']);
    $rows = [];
    $y = 240;

    foreach ($simulator['sections'] as $section) {
        $rows[] = '<text x="80" y="' . $y . '" font-size="24" font-weight="700" fill="' . $colors['secondary'] . '">' . svg_escape(strtoupper($section['label'])) . '</text>';
        $y += 36;

        foreach ($section['groups'] as $group) {
            $names = array_map(static fn (array $player): string => $player['name'], $group['selected']);
            $line = $group['label'] . ': ' . ($names === [] ? '—' : implode(', ', $names));

            foreach (svg_wrap_lines($line, 82) as $wrappedLine) {
                $rows[] = '<text x="80" y="' . $y . '" font-size="18" fill="' . $colors['text'] . '">' . svg_escape($wrappedLine) . '</text>';
                $y += 28;
            }

            $y += 8;
        }

        $y += 18;
    }

    $rows[] = '<text x="80" y="1080" font-size="20" fill="' . $colors['surface_alt'] . '">' . svg_escape($team['tagline']) . '</text>';

    return '<?xml version="1.0" encoding="UTF-8"?>'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="1200" viewBox="0 0 1200 1200" role="img" aria-label="' . $title . '">'
        . '<rect width="1200" height="1200" fill="' . $colors['primary'] . '"/>'
        . '<rect x="60" y="60" width="1080" height="1080" rx="32" fill="rgba(255,255,255,0.05)" stroke="' . $colors['surface_alt'] . '" stroke-width="2"/>'
        . '<text x="80" y="120" font-size="28" fill="' . $colors['secondary'] . '" font-weight="700">' . svg_escape(strtoupper($team['city'])) . '</text>'
        . '<text x="80" y="175" font-size="52" fill="' . $colors['text'] . '" font-weight="800">' . $title . '</text>'
        . '<text x="80" y="210" font-size="24" fill="' . $colors['surface_alt'] . '">' . $subtitle . '</text>'
        . implode('', $rows)
        . '</svg>';
}

function svg_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function svg_wrap_lines(string $text, int $maxLength): array
{
    $words = preg_split('/\s+/', trim($text)) ?: [];
    $lines = [];
    $current = '';

    foreach ($words as $word) {
        $candidate = $current === '' ? $word : $current . ' ' . $word;

        if (strlen($candidate) <= $maxLength) {
            $current = $candidate;
            continue;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        $current = $word;
    }

    if ($current !== '') {
        $lines[] = $current;
    }

    return $lines === [] ? [''] : $lines;
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
