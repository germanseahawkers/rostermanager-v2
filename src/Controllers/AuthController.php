<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;

final class AuthController
{
    public function __construct(private readonly array $config)
    {
    }

    public function showLogin(Request $request): Response
    {
        if (Session::get('admin_authenticated') === true) {
            return Response::redirect($this->config['app']['base_path'] . '/admin/players');
        }

        return Response::html(View::make('auth/login', [
            'config' => $this->config,
            'error' => Session::flash('error'),
        ]));
    }

    public function login(Request $request): Response
    {
        $auth = new AuthService($this->config);

        if (!$auth->attempt((string) $request->input('username', ''), (string) $request->input('password', ''))) {
            Session::flash('error', 'Invalid credentials.');
            return Response::redirect($this->config['app']['base_path'] . '/login');
        }

        Session::put('admin_authenticated', true);
        return Response::redirect($this->config['app']['base_path'] . '/admin/players');
    }

    public function logout(Request $request): Response
    {
        Session::destroy();
        Session::start();
        Session::flash('success', 'You have been logged out.');

        return Response::redirect($this->config['app']['base_path'] . '/login');
    }
}
