<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
        $name = $_ENV['DB_NAME'] ?? $_ENV['MYSQLDATABASE'] ?? 'books_api';
        $user = $_ENV['DB_USER'] ?? $_ENV['MYSQLUSER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log('[DB] ' . $e->getMessage());
            throw new RuntimeException('Database connection failed', 500, $e);
        }

        return self::$pdo;
    }
}
