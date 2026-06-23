<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AuditLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function record(
        ?int $actorId,
        string $action,
        ?string $target,
        ?string $ipAddress,
        ?string $detail = null
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_log (actor_id, action, target, ip_address, detail)
             VALUES (:actor, :action, :target, :ip, :detail)'
        );
        $stmt->execute([
            ':actor' => $actorId,
            ':action' => $action,
            ':target' => $target,
            ':ip' => $ipAddress,
            ':detail' => $detail,
        ]);
    }
}
