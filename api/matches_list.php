<?php
// List all matches in waiting status with their participants.

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$pdo = db();
$user = require_session($pdo);

$stmt = $pdo->query(
    "SELECT m.id AS match_id, m.name, m.max_players, m.creator_id, mp.user_id, u.username
     FROM matches m
     LEFT JOIN match_players mp ON mp.match_id = m.id
     LEFT JOIN users u ON mp.user_id = u.id
     WHERE m.status = 'waiting'
     ORDER BY m.id, mp.joined_at"
);

$matches = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = (int)$row['match_id'];
    if (!isset($matches[$id])) {
        $matches[$id] = [
            'id' => $id,
            'name' => $row['name'],
            'max_players' => (int)$row['max_players'],
            'creator_id' => (int)$row['creator_id'],
            'players' => [],
        ];
    }
    if ($row['user_id'] !== null) {
        $matches[$id]['players'][] = [
            'id' => (int)$row['user_id'],
            'username' => $row['username'],
        ];
    }
}

echo json_encode(['user_id' => $user['id'], 'matches' => array_values($matches)], JSON_UNESCAPED_UNICODE);
