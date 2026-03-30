<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;

final class ShareRepository
{
    public function __construct(private readonly PDO $pdo)
    {
        $this->ensureTable();
    }

    public function findByToken(string $token): ?array
    {
        $normalizedToken = trim($token);

        if ($normalizedToken === '') {
            return null;
        }

        $statement = $this->pdo->prepare('SELECT * FROM shares WHERE token = :token LIMIT 1');
        $statement->execute(['token' => $normalizedToken]);
        $share = $statement->fetch();

        return $share ?: null;
    }

    public function findOrCreate(array $playerIds, string $author, string $scheme, string $lang): array
    {
        $normalizedIds = array_values(array_unique(array_map('intval', $playerIds)));
        sort($normalizedIds);

        $payload = [
            'roster' => $normalizedIds,
            'author' => \normalize_share_author($author),
            'scheme' => trim($scheme),
            'lang' => \resolve_locale($lang),
        ];

        $rosterHash = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
        $existing = $this->findByHash($rosterHash);

        if ($existing !== null) {
            return $existing;
        }

        $share = [
            'token' => $this->generateToken(),
            'roster_hash' => $rosterHash,
            'roster_player_ids' => implode(',', $normalizedIds),
            'author' => $payload['author'],
            'scheme' => $payload['scheme'],
            'lang' => $payload['lang'],
        ];

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO shares (token, roster_hash, roster_player_ids, author, scheme, lang)
                 VALUES (:token, :roster_hash, :roster_player_ids, :author, :scheme, :lang)'
            );

            $statement->execute($share);
        } catch (PDOException $exception) {
            $existing = $this->findByHash($rosterHash);

            if ($existing !== null) {
                return $existing;
            }

            throw $exception;
        }

        return $this->findByToken($share['token']) ?? $share;
    }

    private function findByHash(string $rosterHash): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM shares WHERE roster_hash = :roster_hash LIMIT 1');
        $statement->execute(['roster_hash' => $rosterHash]);
        $share = $statement->fetch();

        return $share ?: null;
    }

    private function generateToken(): string
    {
        do {
            $token = rtrim(strtr(base64_encode(random_bytes(9)), '+/', '-_'), '=');
        } while ($this->findByToken($token) !== null);

        return $token;
    }

    private function ensureTable(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS shares (
                id INT AUTO_INCREMENT PRIMARY KEY,
                token VARCHAR(32) NOT NULL UNIQUE,
                roster_hash CHAR(64) NOT NULL UNIQUE,
                roster_player_ids TEXT NOT NULL,
                author VARCHAR(255) DEFAULT \'\',
                scheme VARCHAR(50) DEFAULT \'primary\',
                lang VARCHAR(10) DEFAULT \'en\',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )'
        );
    }
}
