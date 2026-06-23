<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AuditLogRepository;
use App\Repositories\BookRepository;
use App\Validation\Validator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class BookController
{
    use JsonResponse;

    public function __construct(
        private BookRepository $books,
        private AuditLogRepository $audit
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $rows = $this->books->all(
            (string)($params['q'] ?? ''),
            (int)($params['limit'] ?? 0)
        );
        return $this->json($response, ['count' => count($rows), 'data' => $rows]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $book = $this->books->find((int)($args['id'] ?? 0));
        return $book
            ? $this->json($response, $book)
            : $this->json($response, ['error' => 'Not found'], 404);
    }

    public function create(Request $request, Response $response): Response
    {
        $body = (array)($request->getParsedBody() ?? []);
        $errors = $this->validate($body, false);
        if ($errors) {
            return $this->json($response, ['errors' => $errors], 400);
        }
        $auth = (array)$request->getAttribute('auth', []);
        $actorId = (int)($auth['sub'] ?? 0);
        $id = $this->books->create($body, $actorId);
        $this->audit->record(
            $actorId,
            'book.create',
            "book:{$id}",
            $this->ip($request)
        );
        return $this->json($response, [
            'message' => 'Book created',
            'data' => $this->books->find($id),
        ], 201)->withHeader('Location', "/api/books/{$id}");
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        $book = $this->books->find($id);
        if (!$book) {
            return $this->json($response, ['error' => 'Not found'], 404);
        }
        $auth = (array)$request->getAttribute('auth', []);
        $actorId = (int)($auth['sub'] ?? 0);
        $isOwner = (int)$book['created_by'] === $actorId;
        $isAdmin = ($auth['role'] ?? 'member') === 'admin';
        if (!$isOwner && !$isAdmin) {
            $this->audit->record(
                $actorId,
                'book.update.denied',
                "book:{$id}",
                $this->ip($request)
            );
            return $this->json($response, ['error' => 'Forbidden'], 403);
        }
        $body = (array)($request->getParsedBody() ?? []);
        $errors = $this->validate($body, true);
        if ($errors) {
            return $this->json($response, ['errors' => $errors], 400);
        }
        $this->books->update($id, $body);
        $this->audit->record(
            $actorId,
            'book.update',
            "book:{$id}",
            $this->ip($request)
        );
        return $this->json($response, [
            'message' => 'Book updated',
            'data' => $this->books->find($id),
        ]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $auth = (array)$request->getAttribute('auth', []);
        if (($auth['role'] ?? 'member') !== 'admin') {
            return $this->json($response, ['error' => 'Admins only'], 403);
        }
        $id = (int)($args['id'] ?? 0);
        $book = $this->books->find($id);
        if (!$book) {
            return $this->json($response, ['error' => 'Not found'], 404);
        }
        $this->books->delete($id);
        $this->audit->record(
            (int)$auth['sub'],
            'book.delete',
            "book:{$id}",
            $this->ip($request)
        );
        return $this->json($response, ['message' => 'Book deleted', 'data' => $book]);
    }

    private function validate(array $body, bool $partial): array
    {
        return (new Validator())
            ->required('title', 'author', 'year')
            ->field('title', Validator::nonEmptyString(200), 'title must be 1-200 chars')
            ->field('author', Validator::nonEmptyString(150), 'author must be 1-150 chars')
            ->field('year', Validator::intRange(1000, (int)date('Y')),
                'year must be 1000 to the current year')
            ->field('genre', Validator::nonEmptyString(80), 'genre must be 1-80 chars')
            ->validate($body, $partial);
    }

    private function ip(Request $request): string
    {
        return (string)($request->getServerParams()['REMOTE_ADDR'] ?? 'unknown');
    }
}
