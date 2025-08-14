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

$pdo = db();
$user = require_session($pdo);

$pdo->beginTransaction();
$stmt = $pdo->prepare('INSERT INTO matches (creator_id, status) VALUES (:uid, :status) RETURNING id');
$stmt->execute([':uid' => $user['id'], ':status' => 'waiting']);
$matchId = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('INSERT INTO match_players (match_id, user_id) VALUES (:mid, :uid)');
$stmt->execute([':mid' => $matchId, ':uid' => $user['id']]);
$pdo->commit();

echo json_encode(['match_id' => $matchId], JSON_UNESCAPED_UNICODE);
