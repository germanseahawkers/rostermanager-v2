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
        $locale = in_array($request->query('lang', 'de'), ['de', 'en'], true) ? $request->query('lang', 'de') : 'de';
        $translations = translations($locale);

        $repository = new PlayerRepository($this->database->pdo());
        $positionFilter = (string) $request->query('position', '');

        $players = $positionFilter !== ''
            ? [$positionFilter => $repository->all($positionFilter)]
            : $repository->groupedByPosition();

        return Response::html(View::make('public/roster', [
            'config' => $this->config,
            'locale' => $locale,
            't' => $translations,
            'playersByPosition' => $players,
            'positionFilter' => $positionFilter,
        ]));
    }
}
