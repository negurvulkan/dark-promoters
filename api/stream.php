<?php
// Simple Server-Sent Events endpoint for game state updates.

declare(strict_types=1);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
$last_id = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? (int)$_SERVER['HTTP_LAST_EVENT_ID'] : 0;
if ($game_id <= 0) {
    http_response_code(400);
    echo "data: {\"error\":\"missing game_id\"}\n\n";
    exit;
}

function db(): PDO {
    $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;dbname=dark_promoters';
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

$pdo = db();
while (true) {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT state_json, version FROM games WHERE id = ?');
    $stmt->execute([$game_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();
    if ($row && (int)$row['version'] > $last_id) {
        $state = $row['state_json'];
        $id = (int)$row['version'];
        echo "id: {$id}\n";
        echo "data: {$state}\n\n";
        @ob_flush();
        @flush();
        $last_id = $id;
    }
    sleep(1);
}
