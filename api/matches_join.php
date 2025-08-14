<?php
// Join an existing match if it's still open.

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid json'], JSON_UNESCAPED_UNICODE);
    exit;
}

$match_id = isset($input['match_id']) ? (int)$input['match_id'] : 0;
if ($match_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'missing match_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

function db(): PDO {
    $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;dbname=dark_promoters';
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

$pdo = db();
$user = require_session($pdo);

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT status FROM matches WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => $match_id]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$match) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'match not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($match['status'] !== 'waiting') {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'match not joinable'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt = $pdo->prepare('SELECT user_id FROM match_players WHERE match_id = :mid FOR UPDATE');
    $stmt->execute([':mid' => $match_id]);
    $players = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    if (in_array($user['id'], array_map('intval', $players), true)) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'already joined'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (count($players) >= 4) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'match full'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $insert = $pdo->prepare('INSERT INTO match_players (match_id, user_id) VALUES (:mid, :uid)');
    $insert->execute([':mid' => $match_id, ':uid' => $user['id']]);
    $pdo->commit();
    echo json_encode(['joined' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'server error'], JSON_UNESCAPED_UNICODE);
}
