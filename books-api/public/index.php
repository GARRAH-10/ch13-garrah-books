<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$app = AppFactory::create();
$app->add(new App\Middleware\SecurityHeaders());
$app->add(new App\Middleware\JsonBodyParser());
$app->add(new App\Middleware\Cors());
$app->addRoutingMiddleware();

$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
$app->addErrorMiddleware($debug, true, true);

(require __DIR__ . '/../src/routes.php')($app);
$app->run();
