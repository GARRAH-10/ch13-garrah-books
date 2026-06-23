<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class RateLimit implements MiddlewareInterface
{
    public function __construct(
        private int $limit,
        private int $window,
        private string $bucket = 'default'
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $ip = (string)($request->getServerParams()['REMOTE_ADDR'] ?? 'unknown');
        $safeBucket = preg_replace('/\W+/', '_', $this->bucket);
        $file = sys_get_temp_dir() . "/books-api-rate-{$safeBucket}.json";
        $now = time();
        $data = json_decode((string)@file_get_contents($file), true) ?: [];
        $entry = $data[$ip] ?? ['count' => 0, 'reset' => $now + $this->window];
        if ($entry['reset'] <= $now) {
            $entry = ['count' => 0, 'reset' => $now + $this->window];
        }
        $entry['count']++;
        $data[$ip] = $entry;
        @file_put_contents($file, json_encode($data), LOCK_EX);

        if ($entry['count'] > $this->limit) {
            $response = new Response(429);
            $response->getBody()->write(json_encode(['error' => 'Too many requests']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Retry-After', (string)max(1, $entry['reset'] - $now));
        }
        return $handler->handle($request);
    }
}
