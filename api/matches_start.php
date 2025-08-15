<?php
// Start a match and create a linked game.

declare(strict_types=1);

$smarty = require __DIR__ . '/../src/bootstrap.php';

require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_game.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid json'], JSON_UNESCAPED_UNICODE);
    exit;
}

$match_id = isset($input['match_id']) ? (int)$input['match_id'] : 0;
$ruleset_id = $input['ruleset_id'] ?? 'default.latest';
if ($match_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'missing match_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = db();
$user = require_session($pdo);

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT creator_id, status FROM matches WHERE id = :id FOR UPDATE');
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
        echo json_encode(['error' => 'match not startable'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt = $pdo->prepare('SELECT user_id FROM match_players WHERE match_id = :mid');
    $stmt->execute([':mid' => $match_id]);
    $players = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    if (count($players) < 2) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'not enough players'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $initial_state = [];
    $result = create_game($pdo, $user['id'], $ruleset_id, $initial_state, $match_id, false);
    $game_id = $result['game_id'];

    $insert = $pdo->prepare('INSERT INTO game_players (game_id, user_id) VALUES (:gid, :uid)');
    foreach ($players as $pid) {
        $insert->execute([':gid' => $game_id, ':uid' => $pid]);
    }

    $stmt = $pdo->prepare('UPDATE matches SET status = :status WHERE id = :id');
    $stmt->execute([':status' => 'started', ':id' => $match_id]);

    $pdo->commit();
    echo json_encode(['game_id' => $game_id], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => 'invalid ruleset'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'server error'], JSON_UNESCAPED_UNICODE);
}
