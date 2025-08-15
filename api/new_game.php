<?php
// Create a new game with frozen rules snapshot.

declare(strict_types=1);

$smarty = require __DIR__ . '/../src/bootstrap.php';

require_once __DIR__ . '/_game.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid json']);
    exit;
}

$host_user_id = isset($input['host_user_id']) ? (int)$input['host_user_id'] : 0;
$ruleset_id = $input['ruleset_id'] ?? 'default.latest';
$initial_state = $input['state'] ?? [];
$match_id = isset($input['match_id']) ? (int)$input['match_id'] : null;
$mode = isset($input['mode']) ? (string)$input['mode'] : '';
$ai_count = isset($input['ai_count']) ? (int)$input['ai_count'] : (!empty($input['vs_ai']) ? 1 : 0);

if ($host_user_id <= 0 || !is_array($initial_state) || $mode === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing fields']);
    exit;
}

try {
    $loaded = load_ruleset($ruleset_id);
    if (!isset($loaded['data']['modes'][$mode])) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid mode'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $initial_state['mode'] = $mode;

    $pdo = db();
    $pdo->beginTransaction();
    $result = create_game($pdo, $host_user_id, $ruleset_id, $initial_state, $match_id, $ai_count);
    $pdo->commit();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => 'invalid ruleset'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'server error'], JSON_UNESCAPED_UNICODE);
}
