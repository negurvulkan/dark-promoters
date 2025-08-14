<?php
declare(strict_types=1);

if (!function_exists('db')) {
    require_once __DIR__ . '/../../db.php';
}
require_once __DIR__ . '/../_auth.php';

header('Content-Type: application/json');

$db = db();
require_admin($db);

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}
$id = isset($input['id']) ? (int)$input['id'] : 0;
$username = isset($input['username']) ? trim((string)$input['username']) : null;
$isAdmin = isset($input['is_admin']) ? (int)$input['is_admin'] : null;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid id'], JSON_UNESCAPED_UNICODE);
    exit;
}
$fields = [];
$params = [':id' => $id];
if ($username !== null && $username !== '') {
    $fields[] = 'username = :username';
    $params[':username'] = $username;
}
if ($isAdmin !== null) {
    $fields[] = 'is_admin = :is_admin';
    $params[':is_admin'] = $isAdmin;
}
if ($fields) {
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
}

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
