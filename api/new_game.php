<?php
// Create a new game with frozen rules snapshot.

declare(strict_types=1);

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid json']);
    exit;
}

$host_user_id = isset($input['host_user_id']) ? (int)$input['host_user_id'] : 0;
$ruleset_id = $input['ruleset_id'] ?? '';
$rules_snapshot = $input['rules_json_snapshot'] ?? null;
$initial_state = $input['state'] ?? [];

if ($host_user_id <= 0 || !$ruleset_id || !is_array($rules_snapshot)) {
    http_response_code(400);
    echo json_encode(['error' => 'missing fields']);
    exit;
}

$initial_state['version'] = $initial_state['version'] ?? 0;

function db(): PDO {
    $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;dbname=dark_promoters';
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

$pdo = db();
$pdo->beginTransaction();
$stmt = $pdo->prepare('INSERT INTO games (host_user_id, state_json, version, ruleset_id, rules_json_snapshot) VALUES (:host, :state, 0, :ruleset, :rules) RETURNING id');
$stmt->execute([
    ':host' => $host_user_id,
    ':state' => json_encode($initial_state, JSON_UNESCAPED_UNICODE),
    ':ruleset' => $ruleset_id,
    ':rules' => json_encode($rules_snapshot, JSON_UNESCAPED_UNICODE),
]);
$game_id = (int)$stmt->fetchColumn();
$pdo->commit();

echo json_encode(['game_id' => $game_id, 'state' => $initial_state], JSON_UNESCAPED_UNICODE);
