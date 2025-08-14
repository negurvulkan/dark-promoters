<?php
declare(strict_types=1);

if (!function_exists('db')) {
    require_once __DIR__ . '/../../db.php';
}
require_once __DIR__ . '/../_auth.php';

header('Content-Type: application/json');

$db = db();
require_admin($db);

$stmt = $db->query('SELECT id, username, is_admin, points FROM users ORDER BY id');
$users = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $users[] = [
        'id' => (int)$row['id'],
        'username' => $row['username'],
        'is_admin' => (int)$row['is_admin'],
        'points' => (int)$row['points'],
    ];
}

echo json_encode(['users' => $users], JSON_UNESCAPED_UNICODE);
