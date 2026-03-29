<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;

final class AuthMiddleware
{
    public function __construct(private readonly array $config)
    {
    }

    public function handle(): ?Response
    {
        if (Session::get('admin_authenticated') === true) {
            return null;
        }

        Session::flash('error', 'Please log in to continue.');
        return Response::redirect($this->config['app']['base_path'] . '/login');
    }
}
