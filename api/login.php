<?php
// Authenticate user and create session token.

declare(strict_types=1);

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

function db(): PDO {
    $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;dbname=dark_promoters';
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
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
$stmt = $pdo->prepare("INSERT INTO sessions (user_id, session_token, expires_at) VALUES (:uid, :token, NOW() + INTERVAL '7 days')");
$stmt->execute([
    ':uid' => (int)$user['id'],
    ':token' => $sessionToken,
]);
$pdo->commit();

$response = [
    'session_token' => $sessionToken,
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
    ],
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
