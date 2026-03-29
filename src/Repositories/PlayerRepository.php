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

    public function findManyByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $statement = $this->pdo->prepare('SELECT * FROM players WHERE id IN (' . $placeholders . ')');
        $statement->execute($ids);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM players WHERE id = :id');
        $statement->execute(['id' => $id]);
        $player = $statement->fetch();

        return $player ?: null;
    }

    public function exists(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM players WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        return $statement->fetchColumn() !== false;
    }

    public function create(array $data): void
    {
        if (isset($data['id']) && $data['id'] !== null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO players (id, name, position, experience, weight_kg, height_cm, image, ordering)
                 VALUES (:id, :name, :position, :experience, :weight_kg, :height_cm, :image, :ordering)'
            );

            $statement->execute([
                'id' => (int) $data['id'],
                'name' => $data['name'],
                'position' => $data['position'],
                'experience' => $data['experience'],
                'weight_kg' => $data['weight_kg'],
                'height_cm' => $data['height_cm'],
                'image' => $data['image'],
                'ordering' => $data['ordering'],
            ]);
            return;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO players (name, position, experience, weight_kg, height_cm, image, ordering)
             VALUES (:name, :position, :experience, :weight_kg, :height_cm, :image, :ordering)'
        );

        $statement->execute([
            'name' => $data['name'],
            'position' => $data['position'],
            'experience' => $data['experience'],
            'weight_kg' => $data['weight_kg'],
            'height_cm' => $data['height_cm'],
            'image' => $data['image'],
            'ordering' => $data['ordering'],
        ]);
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE players
             SET name = :name, position = :position, experience = :experience,
                 weight_kg = :weight_kg, height_cm = :height_cm, image = :image, ordering = :ordering
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'name' => $data['name'],
            'position' => $data['position'],
            'experience' => $data['experience'],
            'weight_kg' => $data['weight_kg'],
            'height_cm' => $data['height_cm'],
            'image' => $data['image'],
            'ordering' => $data['ordering'],
        ]);
    }

    public function updateWithoutOrdering(int $id, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE players
             SET name = :name, position = :position, experience = :experience,
                 weight_kg = :weight_kg, height_cm = :height_cm, image = :image
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'name' => $data['name'],
            'position' => $data['position'],
            'experience' => $data['experience'],
            'weight_kg' => $data['weight_kg'],
            'height_cm' => $data['height_cm'],
            'image' => $data['image'],
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM players WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function deleteMissingIds(array $idsToKeep): int
    {
        $idsToKeep = array_values(array_unique(array_map('intval', $idsToKeep)));

        if ($idsToKeep === []) {
            return 0;
        }

        $placeholders = implode(', ', array_fill(0, count($idsToKeep), '?'));
        $statement = $this->pdo->prepare('DELETE FROM players WHERE id NOT IN (' . $placeholders . ')');
        $statement->execute($idsToKeep);

        return $statement->rowCount();
    }

    public function import(array $rows): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
        ];
        $hasIds = false;

        $this->pdo->beginTransaction();

        try {
            foreach ($rows as $row) {
                if (isset($row['id']) && $row['id'] !== null) {
                    $hasIds = true;
                    break;
                }
            }

            foreach ($rows as $row) {
                if ($hasIds) {
                    $id = (int) ($row['id'] ?? 0);

                    if ($id <= 0) {
                        throw new \RuntimeException('ID-based imports require a valid id value in every row.');
                    }

                    if ($this->exists($id)) {
                        $this->updateWithoutOrdering($id, $row);
                        $stats['updated']++;
                        continue;
                    }

                    $this->create($row);
                    $stats['created']++;
                    continue;
                }

                $this->create($row);
                $stats['created']++;
            }

            if ($hasIds) {
                $stats['deleted'] = $this->deleteMissingIds(array_map(
                    static fn (array $row): int => (int) $row['id'],
                    $rows
                ));
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }

        return $stats;
    }
}
