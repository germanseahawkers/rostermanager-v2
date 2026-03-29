<?php

declare(strict_types=1);

use App\Core\Request;

function supported_locales(): array
{
    return [
        'de' => 'DE',
        'en' => 'EN',
        'es' => 'ES',
        'fr' => 'FR',
        'pt' => 'PT',
    ];
}

function resolve_locale(mixed $value, string $default = 'de'): string
{
    $locale = is_string($value) ? strtolower(trim($value)) : '';
    $supported = supported_locales();

    return array_key_exists($locale, $supported) ? $locale : $default;
}

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
            'group_offense' => 'Offense',
            'group_defense' => 'Defense',
            'group_special_teams' => 'Special Teams',
            'share_card_subtitle' => 'Mein Cutdown-Roster',
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
            'group_offense' => 'Offense',
            'group_defense' => 'Defense',
            'group_special_teams' => 'Special Teams',
            'share_card_subtitle' => 'My cutdown roster',
        ],
        'es' => [
            'nav_roster' => 'Simulador',
            'nav_admin' => 'Admin',
            'headline' => 'Simulador del roster de 53 jugadores',
            'subline' => 'Crea tu roster final de recortes a partir del plantel completo de 90 jugadores.',
            'intro_title' => 'De 90 a 53',
            'intro_body' => 'Elige jugadores del roster completo del campamento, sigue en vivo tu contador de cortes y comparte el resultado con tu comunidad.',
            'tab_available' => 'Jugadores disponibles',
            'tab_selected' => 'Tu roster',
            'share_title' => 'Compartir y debatir',
            'share_body' => 'Cada resultado recibe su propia URL y una imagen para compartir generada automáticamente.',
            'review_title' => 'Revisión',
            'review_body' => 'Revisa tu roster final de 53 y compártelo en WhatsApp, redes sociales o directamente con tu peña.',
            'copy_link' => 'Copiar enlace',
            'open_share' => 'Abrir página compartida',
            'download_card' => 'Abrir imagen compartida',
            'share_whatsapp' => 'Compartir por WhatsApp',
            'share_native' => 'Compartir',
            'available_label' => 'Jugadores disponibles',
            'selected_roster' => 'Tu roster de 53',
            'empty_state' => 'Actualmente no hay jugadores para este grupo de posiciones.',
            'experience' => 'Experiencia',
            'height' => 'Altura',
            'weight' => 'Peso',
            'summary_total' => 'Total',
            'summary_short' => 'de',
            'selected_label' => 'Seleccionados',
            'remaining_label' => 'Restantes',
            'status_complete' => 'Roster completo',
            'status_incomplete' => 'Aún no está completo',
            'built_with' => 'Simulador open source de cortes del roster NFL',
            'made_for' => 'Branding del equipo y lógica de posiciones configurables de forma central',
            'share_page_title' => 'Roster compartido',
            'load_this_roster' => 'Abrir este roster en el simulador',
            'share_caption' => 'Mi roster de 53 para el día de cortes',
            'copy_done' => 'Enlace copiado',
            'review_hint' => 'Consejo: puedes compartir la selección actual en cualquier momento mediante URL.',
            'group_offense' => 'Ataque',
            'group_defense' => 'Defensa',
            'group_special_teams' => 'Equipos especiales',
            'share_card_subtitle' => 'Mi roster de recortes',
        ],
        'fr' => [
            'nav_roster' => 'Simulateur',
            'nav_admin' => 'Admin',
            'headline' => 'Simulateur d’effectif à 53 joueurs',
            'subline' => 'Compose ton effectif final de coupe à partir du groupe complet de 90 joueurs.',
            'intro_title' => 'De 90 à 53',
            'intro_body' => 'Choisis des joueurs dans l’effectif complet du camp, suis ton compteur de coupes en direct et partage le résultat avec ta communauté.',
            'tab_available' => 'Joueurs disponibles',
            'tab_selected' => 'Ton effectif',
            'share_title' => 'Partager et débattre',
            'share_body' => 'Chaque résultat reçoit sa propre URL ainsi qu’un visuel de partage généré automatiquement.',
            'review_title' => 'Revue',
            'review_body' => 'Vérifie ton effectif final de 53 joueurs et partage-le sur WhatsApp, les réseaux sociaux ou directement avec ton fan club.',
            'copy_link' => 'Copier le lien',
            'open_share' => 'Ouvrir la page de partage',
            'download_card' => 'Ouvrir le visuel de partage',
            'share_whatsapp' => 'Partager sur WhatsApp',
            'share_native' => 'Partager',
            'available_label' => 'Joueurs disponibles',
            'selected_roster' => 'Ton effectif de 53',
            'empty_state' => 'Aucun joueur n’est actuellement disponible pour ce groupe de positions.',
            'experience' => 'Expérience',
            'height' => 'Taille',
            'weight' => 'Poids',
            'summary_total' => 'Total',
            'summary_short' => 'sur',
            'selected_label' => 'Sélectionnés',
            'remaining_label' => 'Restants',
            'status_complete' => 'Effectif complet',
            'status_incomplete' => 'Pas encore complet',
            'built_with' => 'Simulateur open source de coupe d’effectif NFL',
            'made_for' => 'Branding d’équipe et logique de positions configurables de façon centralisée',
            'share_page_title' => 'Effectif partagé',
            'load_this_roster' => 'Ouvrir cet effectif dans le simulateur',
            'share_caption' => 'Mon effectif de 53 joueurs pour le cutdown day',
            'copy_done' => 'Lien copié',
            'review_hint' => 'Astuce : tu peux partager la sélection actuelle à tout moment via URL.',
            'group_offense' => 'Attaque',
            'group_defense' => 'Défense',
            'group_special_teams' => 'Équipes spéciales',
            'share_card_subtitle' => 'Mon effectif final',
        ],
        'pt' => [
            'nav_roster' => 'Simulador',
            'nav_admin' => 'Admin',
            'headline' => 'Simulador do elenco de 53 jogadores',
            'subline' => 'Monte o seu elenco final de cortes a partir do grupo completo de 90 jogadores.',
            'intro_title' => 'De 90 para 53',
            'intro_body' => 'Escolha jogadores do elenco completo do camp, acompanhe ao vivo o seu contador de cortes e compartilhe o resultado com a sua comunidade.',
            'tab_available' => 'Jogadores disponíveis',
            'tab_selected' => 'Seu elenco',
            'share_title' => 'Compartilhar e discutir',
            'share_body' => 'Cada resultado recebe sua própria URL e uma arte de compartilhamento gerada automaticamente.',
            'review_title' => 'Revisão',
            'review_body' => 'Revise seu elenco final de 53 jogadores e compartilhe no WhatsApp, nas redes sociais ou diretamente com o seu fã-clube.',
            'copy_link' => 'Copiar link',
            'open_share' => 'Abrir página compartilhada',
            'download_card' => 'Abrir arte de compartilhamento',
            'share_whatsapp' => 'Compartilhar no WhatsApp',
            'share_native' => 'Compartilhar',
            'available_label' => 'Jogadores disponíveis',
            'selected_roster' => 'Seu elenco de 53',
            'empty_state' => 'No momento não há jogadores disponíveis para este grupo de posições.',
            'experience' => 'Experiência',
            'height' => 'Altura',
            'weight' => 'Peso',
            'summary_total' => 'Total',
            'summary_short' => 'de',
            'selected_label' => 'Selecionados',
            'remaining_label' => 'Restantes',
            'status_complete' => 'Elenco completo',
            'status_incomplete' => 'Ainda não está completo',
            'built_with' => 'Simulador open source de cortes de elenco da NFL',
            'made_for' => 'Branding do time e lógica de posições configuráveis de forma central',
            'share_page_title' => 'Elenco compartilhado',
            'load_this_roster' => 'Abrir este elenco no simulador',
            'share_caption' => 'Meu elenco de 53 jogadores para o cutdown day',
            'copy_done' => 'Link copiado',
            'review_hint' => 'Dica: você pode compartilhar a seleção atual a qualquer momento por URL.',
            'group_offense' => 'Ataque',
            'group_defense' => 'Defesa',
            'group_special_teams' => 'Times especiais',
            'share_card_subtitle' => 'Meu elenco final',
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
    $fallbackLocale = $locale === 'de' ? 'de' : 'en';

    foreach ($groups as $group) {
        $labelKey = $fallbackLocale === 'de' ? 'label_de' : 'label_en';
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
    $t = translations($locale);
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
        $player['height_cm'] = metric_height_cm($player);
        $player['weight_kg'] = metric_weight_kg($player);
        $playersByGroup[$groupKey]['players'][] = $player;

        if (isset($selectedLookup[(int) $player['id']])) {
            $playersByGroup[$groupKey]['selected'][] = $player;
            $selectedPlayers[] = $player;
        }
    }

    $sections = [
        'offense' => ['key' => 'offense', 'label' => $t['group_offense'], 'groups' => []],
        'defense' => ['key' => 'defense', 'label' => $t['group_defense'], 'groups' => []],
        'special_teams' => ['key' => 'special_teams', 'label' => $t['group_special_teams'], 'groups' => []],
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
    $t = translations($locale);
    $title = svg_escape($team['name'] . ' 53-Man');
    $subtitle = svg_escape($t['share_card_subtitle'] . ' · ' . count($simulator['selected_ids']) . '/' . $simulator['roster_limit']);
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
        'id' => $request->input('id', ''),
        'name' => $request->input('name', ''),
        'position' => $request->input('position', ''),
        'experience' => $request->input('experience', ''),
        'weight_kg' => $request->input('weight_kg', $request->input('weight', '')),
        'height_cm' => $request->input('height_cm', $request->input('height', '')),
        'image' => $request->input('image', ''),
        'ordering' => $request->input('ordering', '0'),
    ]);
}

function player_payload_with_uploaded_image(Request $request, ?array $existingPlayer = null): array
{
    $payload = normalizePlayerPayload($request);
    $uploadedImagePath = handle_player_image_upload($request->file('image_upload'));

    if ($uploadedImagePath !== null) {
        $payload['image'] = $uploadedImagePath;
        return $payload;
    }

    if (($payload['image'] ?? '') !== '') {
        return $payload;
    }

    $payload['image'] = (string) ($existingPlayer['image'] ?? '');

    return $payload;
}

function handle_player_image_upload(?array $file): ?string
{
    if ($file === null) {
        return null;
    }

    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($errorCode !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The player image upload failed.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('The uploaded player image is invalid.');
    }

    $mimeType = mime_content_type($tmpName) ?: '';
    $extension = match ($mimeType) {
        'image/jpeg', 'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => null,
    };

    if ($extension === null) {
        throw new RuntimeException('Only JPG, PNG and WebP player images are supported.');
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
    $targetPath = player_upload_directory() . '/' . $filename;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        throw new RuntimeException('The player image could not be stored.');
    }

    return 'uploads/players/' . $filename;
}

function player_upload_directory(): string
{
    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/players';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('The player upload directory could not be created.');
    }

    return $uploadDir;
}

function import_player_images_zip(?array $file): array
{
    if ($file === null) {
        return ['map' => [], 'count' => 0, 'stored_paths' => []];
    }

    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        return ['map' => [], 'count' => 0, 'stored_paths' => []];
    }

    if ($errorCode !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The ZIP upload failed.');
    }

    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('ZIP imports require the PHP ZipArchive extension.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('The uploaded ZIP archive is invalid.');
    }

    $archive = new ZipArchive();

    if ($archive->open($tmpName) !== true) {
        throw new RuntimeException('The ZIP archive could not be opened.');
    }

    $exactMap = [];
    $basenameMap = [];
    $basenameConflicts = [];
    $storedPaths = [];
    $storedCount = 0;

    for ($index = 0; $index < $archive->numFiles; $index++) {
        $entryName = (string) $archive->getNameIndex($index);
        $normalizedEntry = normalize_import_image_reference($entryName);

        if ($normalizedEntry === '' || str_ends_with($normalizedEntry, '/')) {
            continue;
        }

        if (str_starts_with($normalizedEntry, '__MACOSX/') || str_starts_with(basename($normalizedEntry), '.')) {
            continue;
        }

        $contents = $archive->getFromIndex($index);

        if (!is_string($contents) || $contents === '') {
            continue;
        }

        $relativePath = store_imported_player_image_contents($contents, basename($normalizedEntry));
        $storedPaths[] = $relativePath;
        $exactMap[$normalizedEntry] = $relativePath;

        $basename = basename($normalizedEntry);

        if (!isset($basenameConflicts[$basename])) {
            if (isset($basenameMap[$basename])) {
                unset($basenameMap[$basename]);
                $basenameConflicts[$basename] = true;
            } else {
                $basenameMap[$basename] = $relativePath;
            }
        }

        $storedCount++;
    }

    $archive->close();

    if ($storedCount === 0) {
        throw new RuntimeException('The ZIP archive did not contain any valid JPG, PNG or WebP images.');
    }

    return [
        'map' => array_merge($exactMap, $basenameMap),
        'count' => $storedCount,
        'stored_paths' => $storedPaths,
    ];
}

function normalize_import_image_reference(string $value): string
{
    $normalized = trim(str_replace('\\', '/', $value));

    while (str_starts_with($normalized, './')) {
        $normalized = substr($normalized, 2);
    }

    return ltrim($normalized, '/');
}

function store_imported_player_image_contents(string $contents, string $originalName): string
{
    $mimeType = null;

    if (function_exists('finfo_buffer')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mimeType = finfo_buffer($finfo, $contents) ?: null;
            finfo_close($finfo);
        }
    }

    $extension = match ($mimeType) {
        'image/jpeg', 'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => null,
    };

    if ($extension === null) {
        $suffix = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $extension = match ($suffix) {
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            default => null,
        };
    }

    if ($extension === null) {
        throw new RuntimeException(sprintf('Unsupported image in ZIP archive: %s', $originalName));
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
    $targetPath = player_upload_directory() . '/' . $filename;

    if (file_put_contents($targetPath, $contents) === false) {
        throw new RuntimeException(sprintf('Could not store imported image: %s', $originalName));
    }

    return 'uploads/players/' . $filename;
}

function resolve_imported_player_image(array $row, array $imageMap): array
{
    $image = trim((string) ($row['image'] ?? ''));

    if ($image === '' || $imageMap === []) {
        return $row;
    }

    if (preg_match('#^https?://#i', $image) === 1) {
        return $row;
    }

    $normalizedImage = normalize_import_image_reference($image);

    if ($normalizedImage !== '' && isset($imageMap[$normalizedImage])) {
        $row['image'] = $imageMap[$normalizedImage];
        return $row;
    }

    if (!str_starts_with($normalizedImage, 'uploads/players/')) {
        throw new RuntimeException(sprintf('Image reference not found in ZIP archive: %s', $image));
    }

    return $row;
}

function cleanup_imported_player_images(array $relativePaths): void
{
    $basePath = dirname(__DIR__, 2) . '/public/';

    foreach ($relativePaths as $relativePath) {
        $normalizedPath = ltrim((string) $relativePath, '/');

        if ($normalizedPath === '' || !str_starts_with($normalizedPath, 'uploads/players/')) {
            continue;
        }

        $absolutePath = $basePath . $normalizedPath;

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}

function public_asset_url(string $path, array $config): string
{
    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    $normalizedPath = ltrim($path, '/');
    $basePath = rtrim((string) ($config['app']['base_path'] ?? ''), '/');

    return $basePath . '/' . $normalizedPath;
}

function normalizePlayerArray(array $input): array
{
    $id = null;
    if (array_key_exists('id', $input)) {
        $rawId = trim((string) ($input['id'] ?? ''));
        if ($rawId !== '') {
            if (!ctype_digit($rawId)) {
                throw new RuntimeException(sprintf('Invalid player id: %s', $rawId));
            }
            $id = (int) $rawId;
        }
    }

    $hasMetricHeight = array_key_exists('height_cm', $input) && (string) $input['height_cm'] !== '';
    $hasMetricWeight = array_key_exists('weight_kg', $input) && (string) $input['weight_kg'] !== '';
    $heightCm = parse_height_to_cm(
        (string) ($input['height_cm'] ?? $input['height'] ?? ''),
        $hasMetricHeight ? 'metric' : 'legacy'
    );
    $weightKg = parse_weight_to_kg(
        (string) ($input['weight_kg'] ?? $input['weight'] ?? ''),
        $hasMetricWeight ? 'metric' : 'legacy'
    );

    $normalized = [
        'name' => trim((string) ($input['name'] ?? '')),
        'position' => strtoupper(trim((string) (($input['position'] ?? '') !== '' ? $input['position'] : ($input['abbr'] ?? '')))),
        'experience' => trim((string) ($input['experience'] ?? '')),
        'weight_kg' => $weightKg === null ? '' : (string) $weightKg,
        'height_cm' => $heightCm === null ? '' : (string) $heightCm,
        'image' => trim((string) ($input['image'] ?? '')),
        'ordering' => (int) ($input['ordering'] ?? 0),
    ];

    if ($id !== null) {
        $normalized['id'] = $id;
    }

    return $normalized;
}

function import_rows_use_ids(array $rows): bool
{
    $rowsWithId = 0;

    foreach ($rows as $row) {
        if (isset($row['id']) && $row['id'] !== null) {
            $rowsWithId++;
        }
    }

    if ($rowsWithId === 0) {
        return false;
    }

    if ($rowsWithId !== count($rows)) {
        throw new RuntimeException('Mass import with IDs requires an id value in every CSV row.');
    }

    return true;
}

function metric_height_cm(array $player): ?int
{
    $value = (string) ($player['height_cm'] ?? $player['height'] ?? '');
    return parse_height_to_cm($value, 'metric') ?? parse_height_to_cm($value, 'legacy');
}

function metric_weight_kg(array $player): ?int
{
    return parse_weight_to_kg((string) ($player['weight_kg'] ?? $player['weight'] ?? ''), 'metric');
}

function parse_height_to_cm(string $value, string $mode = 'metric'): ?int
{
    $value = trim($value);

    if ($value === '') {
        return null;
    }

    if (ctype_digit($value)) {
        return (int) $value;
    }

    if (preg_match('/^(\d+)\s*ft\s*(\d+)\s*in$/i', $value, $matches) === 1) {
        $feet = (int) $matches[1];
        $inches = (int) $matches[2];
        return (int) round(($feet * 30.48) + ($inches * 2.54));
    }

    if (preg_match('/^(\d+(?:[.,]\d+)?)\s*m$/i', $value, $matches) === 1) {
        return (int) round((float) str_replace(',', '.', $matches[1]) * 100);
    }

    if (preg_match('/^(\d+(?:[.,]\d+)?)\s*cm$/i', $value, $matches) === 1) {
        return (int) round((float) str_replace(',', '.', $matches[1]));
    }

    if ($mode === 'legacy' && preg_match('/^(\d+)-(\d+)$/', $value, $matches) === 1) {
        $feet = (int) $matches[1];
        $inches = (int) $matches[2];
        return (int) round(($feet * 30.48) + ($inches * 2.54));
    }

    return null;
}

function parse_weight_to_kg(string $value, string $mode = 'metric'): ?int
{
    $value = trim($value);

    if ($value === '') {
        return null;
    }

    if (ctype_digit($value)) {
        $numeric = (int) $value;
        if ($mode === 'legacy') {
            return (int) round($numeric * 0.45359237);
        }
        return $numeric;
    }

    if (preg_match('/^(\d+(?:[.,]\d+)?)\s*kg$/i', $value, $matches) === 1) {
        return (int) round((float) str_replace(',', '.', $matches[1]));
    }

    if (preg_match('/^(\d+(?:[.,]\d+)?)\s*(?:lb|lbs)$/i', $value, $matches) === 1) {
        return (int) round((float) str_replace(',', '.', $matches[1]) * 0.45359237);
    }

    return null;
}

function emptyPlayer(): array
{
    return [
        'id' => null,
        'name' => '',
        'position' => '',
        'experience' => '',
        'weight_kg' => '',
        'height_cm' => '',
        'image' => '',
        'ordering' => 0,
    ];
}
