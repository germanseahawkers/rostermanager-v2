<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\AuthMiddleware;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler, bool $authRequired = false): void
    {
        $this->add('GET', $path, $handler, $authRequired);
    }

    public function post(string $path, callable $handler, bool $authRequired = false): void
    {
        $this->add('POST', $path, $handler, $authRequired);
    }

    public function dispatch(Request $request, Database $database, array $config): Response
    {
        $method = $request->method();
        $path = $request->path();

        if ($method === 'POST' && !csrf_is_valid((string) $request->input('_token', ''))) {
            return Response::html(View::make('layouts/error', [
                'config' => $config,
                'title' => 'Invalid request',
                'message' => 'The form token is invalid or expired. Please reload the page and try again.',
            ]), 419);
        }

        $route = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            return Response::html(View::make('layouts/error', [
                'title' => 'Not found',
                'message' => 'The requested page could not be found.',
            ]), 404);
        }

        if ($route['authRequired']) {
            $middleware = new AuthMiddleware($config);
            $redirect = $middleware->handle();

            if ($redirect !== null) {
                return $redirect;
            }
        }

        return call_user_func($route['handler'], $request);
    }

    private function add(string $method, string $path, callable $handler, bool $authRequired): void
    {
        $normalizedPath = rtrim($path, '/') ?: '/';
        $this->routes[$method][$normalizedPath] = [
            'handler' => $handler,
            'authRequired' => $authRequired,
        ];
    }
}
