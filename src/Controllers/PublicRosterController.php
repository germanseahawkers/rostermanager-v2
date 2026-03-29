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
        $locale = in_array($request->query('lang', 'de'), ['de', 'en'], true) ? (string) $request->query('lang', 'de') : 'de';
        $translations = translations($locale);
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
            't' => $translations,
            'simulator' => $simulator,
            'shareUrl' => $this->config['app']['base_path'] . '/share?lang=' . $locale,
        ]));
    }

    public function share(Request $request): Response
    {
        $locale = in_array($request->query('lang', 'de'), ['de', 'en'], true) ? (string) $request->query('lang', 'de') : 'de';
        $translations = translations($locale);
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

        return Response::html(View::make('public/share', [
            'config' => $this->config,
            'locale' => $locale,
            't' => $translations,
            'simulator' => $simulator,
            'shareCardUrl' => $this->config['app']['base_path'] . '/share/card.svg?lang=' . $locale . '&roster=' . $rosterQuery,
            'simulatorUrl' => $this->config['app']['base_path'] . '/?lang=' . $locale . '&roster=' . $rosterQuery,
        ]));
    }

    public function shareCard(Request $request): Response
    {
        $locale = in_array($request->query('lang', 'de'), ['de', 'en'], true) ? (string) $request->query('lang', 'de') : 'de';
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
            render_share_card_svg($simulator, $this->config, $locale),
            200,
            ['Content-Type' => 'image/svg+xml; charset=utf-8']
        );
    }
}
