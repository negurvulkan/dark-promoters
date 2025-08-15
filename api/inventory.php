<?php
declare(strict_types=1);

$smarty = require __DIR__ . '/../src/bootstrap.php';

require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$pdo = db();
$user = require_session($pdo);

/**
 * Load all valid card IDs from the /cards directory.
 * Cached per request to avoid repeated filesystem scans.
 */
function load_card_ids(): array {
    static $ids = null;
    if ($ids !== null) {
        return $ids;
    }
    $ids = [];
    $baseDir = realpath(__DIR__ . '/../cards');
    if ($baseDir === false || !is_dir($baseDir)) {
        return $ids;
    }
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
    foreach ($iter as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'json') {
            continue;
        }
        $data = json_decode(file_get_contents($file->getPathname()), true);
        if (!is_array($data) || !isset($data['id'])) {
            continue;
        }
        $ids[$data['id']] = true;
    }
    return $ids;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT points FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $points = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT card_id, qty FROM card_inventory WHERE user_id = ? ORDER BY card_id');
    $stmt->execute([$user['id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $inventory = array_map(function ($r) {
        return [
            'card_id' => $r['card_id'],
            'qty' => (int)$r['qty'],
        ];
    }, $rows);
    echo json_encode(['points' => $points, 'inventory' => $inventory], JSON_UNESCAPED_UNICODE);
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
    $cardIds = load_card_ids();
    if (!preg_match('/^[a-z0-9_]+$/', $card_id) || !isset($cardIds[$card_id])) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid card_id']);
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
