<?php

declare(strict_types=1);

use App\Database;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$pdo = Database::get();
$sql = (string)file_get_contents(__DIR__ . '/../sql/schema.sql');
$sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
$sql = preg_replace('/USE\s+books_api\s*;/i', '', $sql);
$pdo->exec($sql);
echo "Database schema and sample records created.\n";
