<?php
// Stateless endpoint returning game state and version.

declare(strict_types=1);

header('Content-Type: application/json');

$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
if ($game_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'missing game_id']);
    exit;
}

function db(): PDO {
    $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;dbname=dark_promoters';
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

$pdo = db();
$pdo->beginTransaction();
$stmt = $pdo->prepare('SELECT state_json, version, rules_json_snapshot FROM games WHERE id = ?');
$stmt->execute([$game_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$pdo->commit();

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'game not found']);
    exit;
}

$state = json_decode($row['state_json'], true);
$state['version'] = (int)$row['version'];

$response = [
    'state' => $state,
    'rules' => json_decode($row['rules_json_snapshot'], true),
    'version' => (int)$row['version'],
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
