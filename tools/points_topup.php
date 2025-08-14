<?php
declare(strict_types=1);

require_once __DIR__ . '/../api/_points.php';
require_once __DIR__ . '/../db.php';

$pdo = db();

$amount = 100;
$stmt = $pdo->query('SELECT id FROM users');
$userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($userIds as $uid) {
    add_points($pdo, (int)$uid, $amount, 'daily topup');
}
