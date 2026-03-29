<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class App
{
    public function __construct(
        private readonly Router $router,
        private readonly Request $request,
        private readonly array $config,
        private readonly Database $database,
    ) {
    }

    public function run(): void
    {
        try {
            $response = $this->router->dispatch($this->request, $this->database, $this->config);
            $response->send();
        } catch (Throwable $exception) {
            http_response_code(500);
            echo View::make('layouts/error', [
                'config' => $this->config,
                'title' => 'Application error',
                'message' => $this->config['app']['debug']
                    ? $exception->getMessage()
                    : 'Something went wrong. Please check the logs.',
            ]);
        }
    }
}
