<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(private readonly string $basePath = '')
    {
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $normalizedBasePath = rtrim($this->basePath, '/');

        if ($normalizedBasePath !== '' && str_starts_with($path, $normalizedBasePath)) {
            $path = substr($path, strlen($normalizedBasePath)) ?: '/';
        }

        return $path === '' ? '/' : rtrim($path, '/') ?: '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }
}
