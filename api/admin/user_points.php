<?php
declare(strict_types=1);

$smarty = require __DIR__ . '/../../src/bootstrap.php';

if (!function_exists('db')) {
    require_once __DIR__ . '/../../db.php';
}
require_once __DIR__ . '/../_auth.php';
require_once __DIR__ . '/../_points.php';

header('Content-Type: application/json');

$db = db();
require_admin($db);

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}
$id = isset($input['id']) ? (int)$input['id'] : 0;
$delta = isset($input['delta']) ? (int)$input['delta'] : 0;
if ($id <= 0 || $delta === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid parameters'], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($delta > 0) {
    add_points($db, $id, $delta, 'admin adjustment');
} else {
    $ok = spend_points($db, $id, -$delta, 'admin adjustment');
    if (!$ok) {
        http_response_code(400);
        echo json_encode(['error' => 'insufficient points'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
$stmt = $db->prepare('SELECT points FROM users WHERE id = :id');
$stmt->execute([':id' => $id]);
$points = (int)$stmt->fetchColumn();

echo json_encode(['points' => $points], JSON_UNESCAPED_UNICODE);
