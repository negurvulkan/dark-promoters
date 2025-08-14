<?php
// Create a new match and add the creator as first player

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

header('Content-Type: application/json');

function db(): PDO {
    $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;dbname=dark_promoters';
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid json'], JSON_UNESCAPED_UNICODE);
    exit;
}

$name = trim((string)($input['name'] ?? ''));
$max = isset($input['max_players']) ? (int)$input['max_players'] : 0;
if ($name === '' || $max < 2 || $max > 4) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid params'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = db();
$user = require_session($pdo);

$pdo->beginTransaction();
$stmt = $pdo->prepare('INSERT INTO matches (creator_id, status, name, max_players) VALUES (:uid, :status, :name, :max) RETURNING id');
$stmt->execute([
    ':uid' => $user['id'],
    ':status' => 'waiting',
    ':name' => $name,
    ':max' => $max,
]);
$matchId = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('INSERT INTO match_players (match_id, user_id) VALUES (:mid, :uid)');
$stmt->execute([':mid' => $matchId, ':uid' => $user['id']]);
$pdo->commit();

echo json_encode(['match_id' => $matchId], JSON_UNESCAPED_UNICODE);
