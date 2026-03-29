<?php

declare(strict_types=1);

namespace App\Services;

final class AuthService
{
    public function __construct(private readonly array $config)
    {
    }

    public function attempt(string $username, string $password): bool
    {
        return hash_equals($this->config['admin']['username'], trim($username))
            && password_verify($password, $this->config['admin']['password_hash']);
    }
}
