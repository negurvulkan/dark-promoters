<?php
// Invalidate an existing session.

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (stripos($authHeader, 'Bearer ') !== 0) {
    http_response_code(401);
    echo json_encode(['error' => 'missing Authorization header'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sessionToken = trim(substr($authHeader, 7));
if ($sessionToken === '') {
    http_response_code(401);
    echo json_encode(['error' => 'missing session token'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = db();
$stmt = $pdo->prepare('DELETE FROM sessions WHERE session_token = ?');
$stmt->execute([$sessionToken]);

echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
