<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JsonBodyParser implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (stripos($request->getHeaderLine('Content-Type'), 'application/json') === 0) {
            $raw = (string)$request->getBody();
            $decoded = $raw === '' ? [] : json_decode($raw, true);
            $request = $request->withParsedBody(is_array($decoded) ? $decoded : []);
        }
        return $handler->handle($request);
    }
}
