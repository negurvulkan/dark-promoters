<?php
// List all matches in waiting status with their participants.

declare(strict_types=1);

header('Content-Type: application/json');

function db(): PDO {
    $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;dbname=dark_promoters';
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

$pdo = db();

$stmt = $pdo->query(
    "SELECT m.id AS match_id, mp.user_id, u.username
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

echo json_encode(array_values($matches), JSON_UNESCAPED_UNICODE);
