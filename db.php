<?php
declare(strict_types=1);

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $configPath = __DIR__ . '/config.php';
    if (!file_exists($configPath)) {
        throw new RuntimeException('Missing config.php');
    }
    $cfg = require $configPath;
    $host = $cfg['db_host'] ?? 'localhost';
    $port = $cfg['db_port'] ?? '5432';
    $name = $cfg['db_name'] ?? 'dark_promoters';
    $user = $cfg['db_user'] ?? '';
    $pass = $cfg['db_pass'] ?? '';
    $dsn = "pgsql:host={$host};port={$port};dbname={$name}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    return $pdo;
}
