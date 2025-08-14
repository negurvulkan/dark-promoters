<?php
declare(strict_types=1);

if (!function_exists('db')) {
    require_once __DIR__ . '/../../db.php';
}
require_once __DIR__ . '/../_auth.php';

header('Content-Type: application/json');

$db = db();
require_admin($db);

$stmt = $db->query('SELECT COUNT(*) AS users, SUM(is_admin) AS admins FROM users');
$data = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['users' => 0, 'admins' => 0];

echo json_encode([
    'users' => (int)$data['users'],
    'admins' => (int)$data['admins'],
], JSON_UNESCAPED_UNICODE);
