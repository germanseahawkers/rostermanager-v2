<?php

declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Database;
use App\Core\Env;

require_once __DIR__ . '/../src/Core/Autoloader.php';
require_once __DIR__ . '/../src/Support/helpers.php';

$autoloader = new Autoloader(__DIR__ . '/../src');
$autoloader->register();

Env::load(__DIR__ . '/../.env');
$config = require __DIR__ . '/../config/app.php';

$options = getopt('', ['team-id::', 'download-images', 'help']);

if (isset($options['help'])) {
    fwrite(STDOUT, "Usage: php scripts/import_espn.php [--team-id=26] [--download-images]\n");
    exit(0);
}

$teamId = isset($options['team-id']) ? (int) $options['team-id'] : (int) ($config['team']['espn_id'] ?? 0);
$downloadImages = array_key_exists('download-images', $options);

if ($teamId <= 0) {
    fwrite(STDERR, "Missing ESPN team ID. Set TEAM_ESPN_ID in .env or pass --team-id=<id>.\n");
    exit(1);
}

try {
    $database = new Database($config['db']);
    $result = sync_espn_roster($database->pdo(), $teamId, $downloadImages);
    fwrite(STDOUT, format_espn_import_result_message($result) . PHP_EOL);
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'ESPN import failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
