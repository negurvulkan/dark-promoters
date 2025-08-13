<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT card_id, qty FROM card_inventory WHERE user_id = ? ORDER BY card_id');
    $stmt->execute([$user['id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $inventory = array_map(function ($r) {
        return [
            'card_id' => $r['card_id'],
            'qty' => (int)$r['qty'],
        ];
    }, $rows);
    echo json_encode(['inventory' => $inventory], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid json']);
        exit;
    }
    $card_id = trim((string)($input['card_id'] ?? ''));
    $qty = isset($input['qty']) ? (int)$input['qty'] : 0;
    if ($card_id === '') {
        http_response_code(400);
        echo json_encode(['error' => 'missing card_id']);
        exit;
    }
    if ($qty > 0) {
        $stmt = $pdo->prepare('INSERT INTO card_inventory (user_id, card_id, qty) VALUES (:uid, :cid, :qty) ON CONFLICT (user_id, card_id) DO UPDATE SET qty = EXCLUDED.qty');
        $stmt->execute([
            ':uid' => $user['id'],
            ':cid' => $card_id,
            ':qty' => $qty,
        ]);
    } else {
        $stmt = $pdo->prepare('DELETE FROM card_inventory WHERE user_id = :uid AND card_id = :cid');
        $stmt->execute([
            ':uid' => $user['id'],
            ':cid' => $card_id,
        ]);
        $qty = 0;
    }
    echo json_encode(['card_id' => $card_id, 'qty' => $qty], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'method not allowed']);
