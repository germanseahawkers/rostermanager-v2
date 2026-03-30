<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\PlayerRepository;
use App\Repositories\ShareRepository;

final class PublicRosterController
{
    public function __construct(
        private readonly Database $database,
        private readonly array $config,
    ) {
    }

    public function index(Request $request): Response
    {
        $repository = new PlayerRepository($this->database->pdo());
        $persistedShare = $this->resolveShare($request);
        $locale = resolve_request_locale($request, $persistedShare);
        $translations = translations($locale);
        $author = normalize_share_author($request->query('author', $persistedShare['author'] ?? ''));
        $palette = resolve_share_palette((string) $request->query('scheme', $persistedShare['scheme'] ?? 'primary'), $this->config, $locale);
        $selectedIds = parse_roster_selection((string) $request->query('roster', $persistedShare['roster_player_ids'] ?? ''));
        $simulator = build_simulator_payload(
            $repository->all(),
            $this->config['team']['simulator']['position_groups'],
            $locale,
            $selectedIds,
            (int) $this->config['team']['simulator']['roster_limit']
        );
        $shareLinks = $this->buildShareLinks($persistedShare['token'] ?? null, $locale, $simulator['selected_ids'], $author, $palette['key']);

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
            'shareCreateUrl' => $this->config['app']['base_path'] . '/share/create',
            'initialShareLinks' => $persistedShare !== null ? $shareLinks : null,
        ]));
    }

    public function share(Request $request): Response
    {
        $repository = new PlayerRepository($this->database->pdo());
        $persistedShare = $this->resolveShare($request);
        $locale = resolve_request_locale($request, $persistedShare);
        $translations = translations($locale);
        $author = normalize_share_author($request->query('author', $persistedShare['author'] ?? ''));
        $palette = resolve_share_palette((string) $request->query('scheme', $persistedShare['scheme'] ?? 'primary'), $this->config, $locale);
        $selectedIds = parse_roster_selection((string) $request->query('roster', $persistedShare['roster_player_ids'] ?? ''));
        $simulator = build_simulator_payload(
            $repository->all(),
            $this->config['team']['simulator']['position_groups'],
            $locale,
            $selectedIds,
            (int) $this->config['team']['simulator']['roster_limit']
        );
        $shareLinks = $this->buildShareLinks($persistedShare['token'] ?? null, $locale, $simulator['selected_ids'], $author, $palette['key']);
        $clubLogoPath = trim((string) ($this->config['club']['logo_path'] ?? ''));
        $shareCardUrl = $this->absoluteUrl($request, $shareLinks['share_card_url']);
        $ogImageUrl = $shareCardUrl;

        if ($clubLogoPath !== '') {
            $ogImageUrl = $this->absoluteUrl($request, public_asset_url($clubLogoPath, $this->config));
        }

        return Response::html(View::make('public/share', [
            'config' => $this->config,
            'locale' => $locale,
            't' => $translations,
            'simulator' => $simulator,
            'author' => $author,
            'palette' => $palette,
            'shareUrl' => $this->absoluteUrl($request, $shareLinks['share_url']),
            'shareCardUrl' => $shareCardUrl,
            'simulatorUrl' => $this->absoluteUrl($request, $shareLinks['simulator_url']),
            'ogImageUrl' => $ogImageUrl,
        ]));
    }

    public function shareCard(Request $request): Response
    {
        $repository = new PlayerRepository($this->database->pdo());
        $persistedShare = $this->resolveShare($request);
        $locale = resolve_request_locale($request, $persistedShare);
        $author = normalize_share_author($request->query('author', $persistedShare['author'] ?? ''));
        $palette = resolve_share_palette((string) $request->query('scheme', $persistedShare['scheme'] ?? 'primary'), $this->config, $locale);
        $selectedIds = parse_roster_selection((string) $request->query('roster', $persistedShare['roster_player_ids'] ?? ''));
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

    public function createShare(Request $request): Response
    {
        $repository = new PlayerRepository($this->database->pdo());
        $locale = resolve_locale($request->input('lang', 'en'));
        $author = normalize_share_author($request->input('author', ''));
        $palette = resolve_share_palette((string) $request->input('scheme', 'primary'), $this->config, $locale);
        $selectedIds = parse_roster_selection((string) $request->input('roster', ''));
        $simulator = build_simulator_payload(
            $repository->all(),
            $this->config['team']['simulator']['position_groups'],
            $locale,
            $selectedIds,
            (int) $this->config['team']['simulator']['roster_limit']
        );

        $shareRepository = new ShareRepository($this->database->pdo());
        $share = $shareRepository->findOrCreate($simulator['selected_ids'], $author, $palette['key'], $locale);
        $shareLinks = $this->buildShareLinks($share['token'], $locale, $simulator['selected_ids'], $author, $palette['key']);

        return new Response(
            json_encode($shareLinks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            200,
            ['Content-Type' => 'application/json; charset=utf-8']
        );
    }

    private function resolveShare(Request $request): ?array
    {
        $token = trim((string) $request->query('s', ''));

        if ($token === '') {
            return null;
        }

        $shareRepository = new ShareRepository($this->database->pdo());

        return $shareRepository->findByToken($token);
    }

    private function buildShareLinks(?string $token, string $locale, array $selectedIds, string $author, string $scheme): array
    {
        if ($token !== null && $token !== '') {
            $localeQuery = '&lang=' . rawurlencode($locale);

            return [
                'token' => $token,
                'share_url' => $this->config['app']['base_path'] . '/share?s=' . rawurlencode($token) . $localeQuery,
                'share_card_url' => $this->config['app']['base_path'] . '/share/card.svg?s=' . rawurlencode($token) . $localeQuery,
                'simulator_url' => $this->config['app']['base_path'] . '/?s=' . rawurlencode($token) . $localeQuery,
            ];
        }

        $rosterQuery = implode(',', array_map('intval', $selectedIds));
        $personalizationQuery = '&author=' . rawurlencode($author) . '&scheme=' . rawurlencode($scheme);

        return [
            'token' => null,
            'share_url' => $this->config['app']['base_path'] . '/share?lang=' . $locale . '&roster=' . $rosterQuery . $personalizationQuery,
            'share_card_url' => $this->config['app']['base_path'] . '/share/card.svg?lang=' . $locale . '&roster=' . $rosterQuery . $personalizationQuery,
            'simulator_url' => $this->config['app']['base_path'] . '/?lang=' . $locale . '&roster=' . $rosterQuery . $personalizationQuery,
        ];
    }

    private function absoluteUrl(Request $request, string $path): string
    {
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        return rtrim($request->origin(), '/') . '/' . ltrim($path, '/');
    }
}
