<?php
// Invalidate an existing session.

declare(strict_types=1);

$smarty = require __DIR__ . '/../src/bootstrap.php';

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';

header('Content-Type: application/json');

$authHeader = $_SERVER['HTTP_AUTHORIZATION']
    ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
    ?? '';
if ($authHeader === '' && function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
}
if (stripos($authHeader, 'Bearer ') !== 0) {
    http_response_code(401);
    echo json_encode(['error' => 'missing Authorization header'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sessionToken = trim(substr($authHeader, 7));
if ($sessionToken === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing session token'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = db();
// Ensure the session is valid before attempting to delete.
require_session($pdo);

$stmt = $pdo->prepare('DELETE FROM sessions WHERE session_token = ?');
$stmt->execute([$sessionToken]);

echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);

