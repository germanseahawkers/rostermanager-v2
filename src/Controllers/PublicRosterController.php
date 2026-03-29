<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\PlayerRepository;

final class PublicRosterController
{
    public function __construct(
        private readonly Database $database,
        private readonly array $config,
    ) {
    }

    public function index(Request $request): Response
    {
        $locale = resolve_locale($request->query('lang', 'en'));
        $translations = translations($locale);
        $author = normalize_share_author($request->query('author', ''));
        $palette = resolve_share_palette((string) $request->query('scheme', 'navy'), $this->config, $locale);
        $repository = new PlayerRepository($this->database->pdo());
        $selectedIds = parse_roster_selection((string) $request->query('roster', ''));
        $simulator = build_simulator_payload(
            $repository->all(),
            $this->config['team']['simulator']['position_groups'],
            $locale,
            $selectedIds,
            (int) $this->config['team']['simulator']['roster_limit']
        );

        return Response::html(View::make('public/roster', [
            'config' => $this->config,
            'locale' => $locale,
            'availableLocales' => supported_locales(),
            't' => $translations,
            'simulator' => $simulator,
            'author' => $author,
            'palette' => $palette,
            'paletteOptions' => share_palette_options($this->config, $locale),
            'shareUrl' => $this->config['app']['base_path'] . '/share?lang=' . $locale,
        ]));
    }

    public function share(Request $request): Response
    {
        $locale = resolve_locale($request->query('lang', 'en'));
        $translations = translations($locale);
        $author = normalize_share_author($request->query('author', ''));
        $palette = resolve_share_palette((string) $request->query('scheme', 'navy'), $this->config, $locale);
        $repository = new PlayerRepository($this->database->pdo());
        $selectedIds = parse_roster_selection((string) $request->query('roster', ''));
        $simulator = build_simulator_payload(
            $repository->all(),
            $this->config['team']['simulator']['position_groups'],
            $locale,
            $selectedIds,
            (int) $this->config['team']['simulator']['roster_limit']
        );
        $rosterQuery = implode(',', $simulator['selected_ids']);
        $personalizationQuery = '&author=' . rawurlencode($author) . '&scheme=' . rawurlencode($palette['key']);

        return Response::html(View::make('public/share', [
            'config' => $this->config,
            'locale' => $locale,
            't' => $translations,
            'simulator' => $simulator,
            'author' => $author,
            'palette' => $palette,
            'shareCardUrl' => $this->config['app']['base_path'] . '/share/card.svg?lang=' . $locale . '&roster=' . $rosterQuery . $personalizationQuery,
            'simulatorUrl' => $this->config['app']['base_path'] . '/?lang=' . $locale . '&roster=' . $rosterQuery . $personalizationQuery,
        ]));
    }

    public function shareCard(Request $request): Response
    {
        $locale = resolve_locale($request->query('lang', 'en'));
        $author = normalize_share_author($request->query('author', ''));
        $palette = resolve_share_palette((string) $request->query('scheme', 'navy'), $this->config, $locale);
        $repository = new PlayerRepository($this->database->pdo());
        $selectedIds = parse_roster_selection((string) $request->query('roster', ''));
        $simulator = build_simulator_payload(
            $repository->all(),
            $this->config['team']['simulator']['position_groups'],
            $locale,
            $selectedIds,
            (int) $this->config['team']['simulator']['roster_limit']
        );

        return new Response(
            render_share_card_svg($simulator, $this->config, $locale, $author, $palette),
            200,
            ['Content-Type' => 'image/svg+xml; charset=utf-8']
        );
    }
}
