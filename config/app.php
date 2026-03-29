<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => getenv('APP_NAME') ?: ((getenv('TEAM_NAME') ?: 'NFL Team') . ' Roster Simulator'),
        'base_path' => rtrim(getenv('APP_BASE_PATH') ?: '', '/'),
        'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
    ],
    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: 'rostermanager_v2',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    ],
    'admin' => [
        'username' => getenv('ADMIN_USERNAME') ?: 'admin',
        'password_hash' => getenv('ADMIN_PASSWORD_HASH')
            ?: password_hash(getenv('ADMIN_PASSWORD') ?: 'admin123', PASSWORD_DEFAULT),
    ],
    'team' => require __DIR__ . '/team.php',
];
