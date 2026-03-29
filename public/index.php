<?php

declare(strict_types=1);

use App\Controllers\AdminPlayerController;
use App\Controllers\AuthController;
use App\Controllers\PublicRosterController;
use App\Core\App;
use App\Core\Autoloader;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

require_once __DIR__ . '/../src/Core/Autoloader.php';
require_once __DIR__ . '/../src/Support/helpers.php';

$autoloader = new Autoloader(__DIR__ . '/../src');
$autoloader->register();

$config = require __DIR__ . '/../config/app.php';

Session::start();
$database = new Database($config['db']);
$router = new Router();
$app = new App($router, new Request($config['app']['base_path']), $config, $database);

$publicController = new PublicRosterController($database, $config);
$authController = new AuthController($config);
$adminController = new AdminPlayerController($database, $config);

$router->get('/', [$publicController, 'index']);
$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->post('/logout', [$authController, 'logout']);

$router->get('/admin/players', [$adminController, 'index'], true);
$router->get('/admin/players/create', [$adminController, 'create'], true);
$router->post('/admin/players', [$adminController, 'store'], true);
$router->get('/admin/players/edit', [$adminController, 'edit'], true);
$router->post('/admin/players/update', [$adminController, 'update'], true);
$router->post('/admin/players/delete', [$adminController, 'delete'], true);
$router->post('/admin/players/import', [$adminController, 'importCsv'], true);

$app->run();
