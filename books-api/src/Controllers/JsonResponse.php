<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;

trait JsonResponse
{
    private function json(
        ResponseInterface $response,
        mixed $data,
        int $status = 200
    ): ResponseInterface {
        $response->getBody()->write((string)json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        ));
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
}
