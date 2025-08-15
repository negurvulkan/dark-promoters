<?php
// Add an AI player to a match.

declare(strict_types=1);

$smarty = require __DIR__ . '/../src/bootstrap.php';

require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../db.php';

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

$pdo = db();
$user = require_session($pdo);

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT creator_id, status, max_players FROM matches WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => $match_id]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$match) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'match not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ((int)$match['creator_id'] !== $user['id']) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(['error' => 'not creator'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($match['status'] !== 'waiting') {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'match not joinable'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM match_players WHERE match_id = :mid FOR UPDATE');
    $stmt->execute([':mid' => $match_id]);
    $count = (int)$stmt->fetchColumn();
    if ($count >= (int)$match['max_players']) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'match full'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $aiName = 'AI Bot';
    $insert = $pdo->prepare('INSERT INTO match_players (match_id, username, is_ai) VALUES (:mid, :name, 1)');
    $insert->execute([':mid' => $match_id, ':name' => $aiName]);
    $pdo->commit();
    echo json_encode(['added' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'server error'], JSON_UNESCAPED_UNICODE);
}

