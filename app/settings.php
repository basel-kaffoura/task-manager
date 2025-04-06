<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Load environment variables from .env file if it exists
    if (file_exists(__DIR__.'/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
        $dotenv->load();
    }

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError' => false,
                'logErrorDetails' => false,
                'logger' => [
                    'name' => 'task-manager-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__.'/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'db' => [
                    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                    'port' => $_ENV['DB_PORT'] ?? '5432',
                    'database' => $_ENV['DB_DATABASE'] ?? 'tasks_db',
                    'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
                    'password' => $_ENV['DB_PASSWORD'] ?? 'secret',
                    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8',
                ],
            ]);
        }
    ]);
};