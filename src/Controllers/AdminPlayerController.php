<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\PlayerRepository;
use Throwable;

final class AdminPlayerController
{
    public function __construct(
        private readonly Database $database,
        private readonly array $config,
    ) {
    }

    public function index(Request $request): Response
    {
        $repository = new PlayerRepository($this->database->pdo());

        return Response::html(View::make('admin/players/index', [
            'config' => $this->config,
            'players' => $repository->all(),
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
            'player' => emptyPlayer(),
        ]));
    }

    public function create(Request $request): Response
    {
        return $this->index($request);
    }

    public function store(Request $request): Response
    {
        try {
            $repository = new PlayerRepository($this->database->pdo());
            $repository->create(player_payload_with_uploaded_image($request));
        } catch (Throwable $exception) {
            Session::flash('error', $exception->getMessage());
            return Response::redirect($this->config['app']['base_path'] . '/admin/players');
        }

        Session::flash('success', 'Player created.');
        return Response::redirect($this->config['app']['base_path'] . '/admin/players');
    }

    public function edit(Request $request): Response
    {
        $repository = new PlayerRepository($this->database->pdo());
        $player = $repository->find((int) $request->query('id', 0));

        if ($player === null) {
            Session::flash('error', 'Player not found.');
            return Response::redirect($this->config['app']['base_path'] . '/admin/players');
        }

        return Response::html(View::make('admin/players/index', [
            'config' => $this->config,
            'players' => $repository->all(),
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
            'player' => $player,
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $repository = new PlayerRepository($this->database->pdo());
        $existingPlayer = $repository->find($id);

        if ($existingPlayer === null) {
            Session::flash('error', 'Player not found.');
            return Response::redirect($this->config['app']['base_path'] . '/admin/players');
        }

        try {
            $repository->update($id, player_payload_with_uploaded_image($request, $existingPlayer));
        } catch (Throwable $exception) {
            Session::flash('error', $exception->getMessage());
            return Response::redirect($this->config['app']['base_path'] . '/admin/players/edit?id=' . $id);
        }

        Session::flash('success', 'Player updated.');
        return Response::redirect($this->config['app']['base_path'] . '/admin/players');
    }

    public function delete(Request $request): Response
    {
        $repository = new PlayerRepository($this->database->pdo());
        $repository->delete((int) $request->input('id', 0));

        Session::flash('success', 'Player deleted.');
        return Response::redirect($this->config['app']['base_path'] . '/admin/players');
    }

    public function importCsv(Request $request): Response
    {
        $file = $request->file('csv');

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please upload a valid CSV file.');
            return Response::redirect($this->config['app']['base_path'] . '/admin/players');
        }

        $handle = fopen($file['tmp_name'], 'rb');

        if ($handle === false) {
            Session::flash('error', 'The CSV file could not be read.');
            return Response::redirect($this->config['app']['base_path'] . '/admin/players');
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            Session::flash('error', 'The CSV file is empty.');
            return Response::redirect($this->config['app']['base_path'] . '/admin/players');
        }

        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $line);

            if ($row === false) {
                continue;
            }

            $rows[] = normalizePlayerArray($row);
        }

        fclose($handle);

        $repository = new PlayerRepository($this->database->pdo());
        $count = $repository->import($rows);

        Session::flash('success', sprintf('%d player(s) imported.', $count));
        return Response::redirect($this->config['app']['base_path'] . '/admin/players');
    }
}
