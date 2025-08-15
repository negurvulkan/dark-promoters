<?php
// Authenticate user and create session token.

declare(strict_types=1);

$smarty = require __DIR__ . '/../src/bootstrap.php';

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid json']);
    exit;
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';
if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing fields']);
    exit;
}

$pdo = db();
$stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'invalid credentials']);
    exit;
}

$sessionToken = bin2hex(random_bytes(32));
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO sessions (user_id, session_token, expires_at) VALUES (:uid, :token, DATE_ADD(NOW(), INTERVAL 7 DAY))");
$stmt->execute([
    ':uid' => (int)$user['id'],
    ':token' => $sessionToken,
]);
$pdo->commit();

setcookie('session_token', $sessionToken, [
    'expires' => time() + 60 * 60 * 24 * 7,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

$response = [
    'session_token' => $sessionToken,
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
    ],
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
