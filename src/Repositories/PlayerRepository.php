<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PlayerRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(?string $position = null): array
    {
        $sql = 'SELECT * FROM players';
        $params = [];

        if ($position !== null && $position !== '') {
            $sql .= ' WHERE position = :position';
            $params['position'] = $position;
        }

        $sql .= ' ORDER BY ordering ASC, name ASC';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function groupedByPosition(): array
    {
        $players = $this->all();
        $grouped = [];

        foreach ($players as $player) {
            $grouped[$player['position']][] = $player;
        }

        ksort($grouped);
        return $grouped;
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM players WHERE id = :id');
        $statement->execute(['id' => $id]);
        $player = $statement->fetch();

        return $player ?: null;
    }

    public function create(array $data): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO players (name, position, abbr, experience, weight, height, image, ordering)
             VALUES (:name, :position, :abbr, :experience, :weight, :height, :image, :ordering)'
        );

        $statement->execute($data);
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;

        $statement = $this->pdo->prepare(
            'UPDATE players
             SET name = :name, position = :position, abbr = :abbr, experience = :experience,
                 weight = :weight, height = :height, image = :image, ordering = :ordering
             WHERE id = :id'
        );

        $statement->execute($data);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM players WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function import(array $rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $this->create($row);
            $count++;
        }

        return $count;
    }
}
