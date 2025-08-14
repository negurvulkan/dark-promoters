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
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid id'], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt = $db->prepare('DELETE FROM users WHERE id = :id');
$stmt->execute([':id' => $id]);

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
