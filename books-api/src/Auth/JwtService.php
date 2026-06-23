<?php

declare(strict_types=1);

namespace App\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

final class JwtService
{
    private string $secret;
    private string $algorithm = 'HS256';
    private int $ttl;
    private string $issuer;

    public function __construct()
    {
        $this->secret = (string)($_ENV['JWT_SECRET'] ?? '');
        if ($this->secret === '' || str_starts_with($this->secret, 'replace-')) {
            throw new RuntimeException('JWT_SECRET is not configured');
        }
        $this->ttl = (int)($_ENV['JWT_TTL'] ?? 3600);
        $this->issuer = (string)($_ENV['JWT_ISSUER'] ?? 'books-api');
    }

    public function issue(int $userId, array $extra = []): string
    {
        $now = time();
        $payload = array_merge([
            'iss' => $this->issuer,
            'sub' => $userId,
            'iat' => $now,
            'exp' => $now + $this->ttl,
        ], $extra);
        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function verify(string $token): array
    {
        $payload = (array)JWT::decode($token, new Key($this->secret, $this->algorithm));
        if (($payload['iss'] ?? '') !== $this->issuer) {
            throw new RuntimeException('Invalid token issuer');
        }
        return $payload;
    }

    public function ttl(): int
    {
        return $this->ttl;
    }
}
