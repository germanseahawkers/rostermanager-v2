<?php

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    public function __construct(private readonly string $basePath)
    {
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'load']);
    }

    private function load(string $className): void
    {
        $prefix = 'App\\';

        if (!str_starts_with($className, $prefix)) {
            return;
        }

        $relative = substr($className, strlen($prefix));
        $file = $this->basePath . '/' . str_replace('\\', '/', $relative) . '.php';

        if (is_file($file)) {
            require_once $file;
        }
    }
}
