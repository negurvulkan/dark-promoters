<?php
// Register a new user with unique username.

declare(strict_types=1);

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
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$username]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'username taken']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$pdo->beginTransaction();
$stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :hash) RETURNING id');
$stmt->execute([
    ':username' => $username,
    ':hash' => $hash,
]);
$user_id = (int)$stmt->fetchColumn();
$pdo->commit();

echo json_encode(['user_id' => $user_id, 'username' => $username], JSON_UNESCAPED_UNICODE);
