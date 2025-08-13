<?php
// Invalidate an existing session.

declare(strict_types=1);

header('Content-Type: application/json');

$sessionToken = $_SERVER['HTTP_X_SESSION_TOKEN'] ?? $_SERVER['HTTP_SESSION_TOKEN'] ?? '';
if ($sessionToken === '') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        $sessionToken = trim((string)($input['session_token'] ?? ''));
    }
}

$sessionToken = trim($sessionToken);
if ($sessionToken === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing session_token']);
    exit;
}

function db(): PDO {
    $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;dbname=dark_promoters';
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

$pdo = db();
$stmt = $pdo->prepare('DELETE FROM sessions WHERE session_token = ?');
$stmt->execute([$sessionToken]);

echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
