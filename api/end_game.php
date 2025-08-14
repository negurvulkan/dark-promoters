<?php
// Finalize a game, award winner points, and log the result.

declare(strict_types=1);

require_once __DIR__ . '/_points.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid json']);
    exit;
}

$game_id = isset($input['game_id']) ? (int)$input['game_id'] : 0;
$expected_version = isset($input['expected_version']) ? (int)$input['expected_version'] : -1;
$winner_id = isset($input['winner_id']) ? (int)$input['winner_id'] : 0;
if ($game_id <= 0 || $expected_version < 0 || $winner_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'missing fields']);
    exit;
}

$pdo = db();
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT state_json, version, rules_json_snapshot FROM games WHERE id = ? FOR UPDATE');
    $stmt->execute([$game_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'game not found']);
        exit;
    }
    if ((int)$row['version'] !== $expected_version) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['error' => 'version mismatch']);
        exit;
    }
    $state = json_decode($row['state_json'], true);
    $rules = json_decode($row['rules_json_snapshot'], true);
    $reward = (int)($rules['global']['winReward'] ?? 0);
    if ($reward > 0) {
        add_points($pdo, $winner_id, $reward, 'game win');
        $state['log'][] = [
            'type' => 'points_awarded',
            'user_id' => $winner_id,
            'points' => $reward,
        ];
    }
    $state['winner_id'] = $winner_id;
    $state['version'] = $state['version'] + 1;
    $update = $pdo->prepare('UPDATE games SET state_json = :state, version = version + 1 WHERE id = :id');
    $update->execute([
        ':state' => json_encode($state, JSON_UNESCAPED_UNICODE),
        ':id' => $game_id,
    ]);
    $pdo->commit();
    echo json_encode(['state' => $state], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'server error']);
}
