<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\JwtService;
use App\Repositories\AuditLogRepository;
use App\Repositories\UserRepository;
use App\Validation\Validator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class AuthController
{
    use JsonResponse;

    public function __construct(
        private UserRepository $users,
        private JwtService $jwt,
        private AuditLogRepository $audit
    ) {
    }

    public function register(Request $request, Response $response): Response
    {
        $body = (array)($request->getParsedBody() ?? []);
        $errors = (new Validator())
            ->required('name', 'email', 'password')
            ->field('name', Validator::nonEmptyString(150), 'name must be 1-150 chars')
            ->field('email', Validator::email(), 'invalid email')
            ->field('password', fn($value): bool => is_string($value) && mb_strlen($value) >= 8,
                'password must be at least 8 chars')
            ->validate($body);
        if ($errors) {
            return $this->json($response, ['errors' => $errors], 400);
        }
        if ($this->users->emailExists($body['email'])) {
            return $this->json($response, ['error' => 'Email already registered'], 409);
        }
        $id = $this->users->create(
            $body['name'],
            $body['email'],
            password_hash($body['password'], PASSWORD_DEFAULT)
        );
        $this->audit->record($id, 'register', "user:{$id}", $this->ip($request));
        return $this->json($response, [
            'message' => 'Registered',
            'user' => $this->users->findById($id),
        ], 201);
    }

    public function login(Request $request, Response $response): Response
    {
        $body = (array)($request->getParsedBody() ?? []);
        $user = $this->users->findByEmail((string)($body['email'] ?? ''));
        if (!$user || !password_verify((string)($body['password'] ?? ''), $user['password_hash'])) {
            $this->audit->record(
                $user ? (int)$user['id'] : null,
                'login.fail',
                null,
                $this->ip($request),
                (string)($body['email'] ?? '')
            );
            return $this->json($response, ['error' => 'Invalid credentials'], 401);
        }
        $publicUser = $this->users->findById((int)$user['id']);
        $token = $this->jwt->issue((int)$user['id'], [
            'role' => $user['role'],
            'email' => $user['email'],
        ]);
        $this->audit->record((int)$user['id'], 'login.success', null, $this->ip($request));
        return $this->json($response, [
            'token_type' => 'Bearer',
            'expires_in' => $this->jwt->ttl(),
            'access_token' => $token,
            'user' => $publicUser,
        ]);
    }

    public function me(Request $request, Response $response): Response
    {
        $auth = (array)$request->getAttribute('auth', []);
        $user = $this->users->findById((int)($auth['sub'] ?? 0));
        return $user
            ? $this->json($response, $user)
            : $this->json($response, ['error' => 'Not found'], 404);
    }

    private function ip(Request $request): string
    {
        return (string)($request->getServerParams()['REMOTE_ADDR'] ?? 'unknown');
    }
}
