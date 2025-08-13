<?php
// Apply an action to game state with optimistic locking.

declare(strict_types=1);

require_once __DIR__ . '/update_game.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid json']);
    exit;
}

$game_id = isset($input['game_id']) ? (int)$input['game_id'] : 0;
$expected_version = isset($input['expected_version']) ? (int)$input['expected_version'] : -1;
$action = $input['action'] ?? null;
if ($game_id <= 0 || $expected_version < 0 || !is_array($action)) {
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

function apply_action(array $state, array $action, array $rules): array {
    // Placeholder: append action to log.
    $state['log'][] = $action;
    return $state;
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
    $new_state = apply_action($state, $action, $rules);
    $new_state['version'] = $state['version'] + 1;
    $update = $pdo->prepare('UPDATE games SET state_json = :state, version = version + 1 WHERE id = :id');
    $update->execute([
        ':state' => json_encode($new_state, JSON_UNESCAPED_UNICODE),
        ':id' => $game_id,
    ]);
    $pdo->commit();
    echo json_encode(['state' => $new_state], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'server error']);
}
