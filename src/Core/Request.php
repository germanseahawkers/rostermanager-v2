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

        return $path === '' ? '/' : (rtrim($path, '/') ?: '/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $normalizedKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));

        return $_SERVER[$normalizedKey] ?? $default;
    }

    public function scheme(): string
    {
        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));

        if ($https !== '' && $https !== 'off') {
            return 'https';
        }

        $forwardedProto = trim((string) $this->header('X-Forwarded-Proto', ''));

        if ($forwardedProto !== '') {
            return strtolower(explode(',', $forwardedProto)[0] ?? 'https');
        }

        return 'http';
    }

    public function host(): string
    {
        $forwardedHost = trim((string) $this->header('X-Forwarded-Host', ''));

        if ($forwardedHost !== '') {
            return trim(explode(',', $forwardedHost)[0] ?? '');
        }

        return trim((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost'));
    }

    public function origin(): string
    {
        return $this->scheme() . '://' . $this->host();
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }
}
