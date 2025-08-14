<?php
declare(strict_types=1);

require_once __DIR__ . '/../api/_points.php';

function db(): PDO {
    $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;dbname=dark_promoters';
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

$pdo = db();

$amount = 100;
$stmt = $pdo->query('SELECT id FROM users');
$userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($userIds as $uid) {
    add_points($pdo, (int)$uid, $amount, 'daily topup');
}
