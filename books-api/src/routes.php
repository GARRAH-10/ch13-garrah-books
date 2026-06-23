<?php

declare(strict_types=1);

use App\Auth\JwtService;
use App\Controllers\AuthController;
use App\Controllers\BookController;
use App\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimit;
use App\Repositories\AuditLogRepository;
use App\Repositories\BookRepository;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app): void {
$app->options('/{routes:.+}', function (Request $request, Response $response): Response {
    return $response;
});
    $pdo = Database::get();
    $jwt = new JwtService();
    $auth = new AuthMiddleware($jwt);
    $audit = new AuditLogRepository($pdo);
    $bookController = new BookController(new BookRepository($pdo), $audit);
    $authController = new AuthController(new UserRepository($pdo), $jwt, $audit);
    $loginLimit = new RateLimit(
        (int)($_ENV['LOGIN_RATE_LIMIT'] ?? 5),
        (int)($_ENV['LOGIN_WINDOW_SECONDS'] ?? 60),
        'login'
    );

    $app->get('/', function (Request $request, Response $response): Response {
        $response->getBody()->write(json_encode([
            'status' => 'ok',
            'name' => 'UTM Books REST API',
            'version' => '1.0.0',
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/auth/register', [$authController, 'register']);
    $app->post('/auth/login', [$authController, 'login'])->add($loginLimit);
    $app->get('/auth/me', [$authController, 'me'])->add($auth);

    $app->get('/api/books', [$bookController, 'index']);
    $app->get('/api/books/{id:[0-9]+}', [$bookController, 'show']);
    $app->group('/api/books', function ($group) use ($bookController): void {
        $group->post('', [$bookController, 'create']);
        $group->put('/{id:[0-9]+}', [$bookController, 'update']);
        $group->delete('/{id:[0-9]+}', [$bookController, 'delete']);
    })->add($auth);
};
