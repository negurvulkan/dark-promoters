<?php
declare(strict_types=1);

require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$pdo = db();
$user = require_session($pdo);

// Helper to validate deck cards against inventory
function validate_inventory(PDO $pdo, int $userId, array $cards): void {
    $needed = [];
    foreach ($cards as $c) {
        $cid = trim((string)($c['card_id'] ?? ''));
        $qty = isset($c['qty']) ? (int)$c['qty'] : 0;
        if ($cid === '' || $qty <= 0) {
            continue;
        }
        $needed[$cid] = ($needed[$cid] ?? 0) + $qty;
    }
    if (!$needed) {
        return; // nothing to validate
    }
    $ids = array_keys($needed);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT card_id, qty FROM card_inventory WHERE user_id = ? AND card_id IN ($placeholders)");
    $stmt->execute(array_merge([$userId], $ids));
    $inventory = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $inventory[$row['card_id']] = (int)$row['qty'];
    }
    foreach ($needed as $cid => $qty) {
        if (($inventory[$cid] ?? 0) < $qty) {
            http_response_code(400);
            echo json_encode(['error' => 'insufficient cards', 'card_id' => $cid], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $deckId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($deckId > 0) {
        $stmt = $pdo->prepare('SELECT id, name FROM decks WHERE id = ? AND user_id = ?');
        $stmt->execute([$deckId, $user['id']]);
        $deck = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$deck) {
            http_response_code(404);
            echo json_encode(['error' => 'deck not found']);
            exit;
        }
        $stmt = $pdo->prepare('SELECT card_id, qty FROM deck_cards WHERE deck_id = ? ORDER BY card_id');
        $stmt->execute([$deckId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $cards = array_map(function ($r) {
            return [
                'card_id' => $r['card_id'],
                'qty' => (int)$r['qty'],
            ];
        }, $rows);
        echo json_encode(['id' => (int)$deck['id'], 'name' => $deck['name'], 'cards' => $cards], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        $stmt = $pdo->prepare('SELECT d.id, d.name, COALESCE(SUM(c.qty),0) AS card_count FROM decks d LEFT JOIN deck_cards c ON d.id = c.deck_id WHERE d.user_id = ? GROUP BY d.id ORDER BY d.id');
        $stmt->execute([$user['id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $decks = array_map(function ($r) {
            return [
                'id' => (int)$r['id'],
                'name' => $r['name'],
                'card_count' => (int)$r['card_count'],
            ];
        }, $rows);
        echo json_encode(['decks' => $decks], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid json']);
        exit;
    }
    $name = trim((string)($input['name'] ?? ''));
    $cards = $input['cards'] ?? [];
    if ($name === '' || !is_array($cards)) {
        http_response_code(400);
        echo json_encode(['error' => 'missing fields']);
        exit;
    }
    validate_inventory($pdo, $user['id'], $cards);
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO decks (user_id, name) VALUES (:uid, :name) RETURNING id');
    $stmt->execute([':uid' => $user['id'], ':name' => $name]);
    $deckId = (int)$stmt->fetchColumn();
    $stmt = $pdo->prepare('INSERT INTO deck_cards (deck_id, card_id, qty) VALUES (:did, :cid, :qty)');
    foreach ($cards as $c) {
        $cid = trim((string)($c['card_id'] ?? ''));
        $qty = isset($c['qty']) ? (int)$c['qty'] : 0;
        if ($cid === '' || $qty <= 0) {
            continue;
        }
        $stmt->execute([':did' => $deckId, ':cid' => $cid, ':qty' => $qty]);
    }
    $pdo->commit();
    echo json_encode(['id' => $deckId, 'name' => $name], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid json']);
        exit;
    }
    $deckId = (int)($input['id'] ?? 0);
    $name = trim((string)($input['name'] ?? ''));
    $cards = $input['cards'] ?? [];
    if ($deckId <= 0 || $name === '' || !is_array($cards)) {
        http_response_code(400);
        echo json_encode(['error' => 'missing fields']);
        exit;
    }
    // verify deck ownership
    $stmt = $pdo->prepare('SELECT id FROM decks WHERE id = ? AND user_id = ?');
    $stmt->execute([$deckId, $user['id']]);
    if (!$stmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['error' => 'deck not found']);
        exit;
    }
    validate_inventory($pdo, $user['id'], $cards);
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE decks SET name = :name WHERE id = :id');
    $stmt->execute([':name' => $name, ':id' => $deckId]);
    $pdo->prepare('DELETE FROM deck_cards WHERE deck_id = ?')->execute([$deckId]);
    $insert = $pdo->prepare('INSERT INTO deck_cards (deck_id, card_id, qty) VALUES (:did, :cid, :qty)');
    foreach ($cards as $c) {
        $cid = trim((string)($c['card_id'] ?? ''));
        $qty = isset($c['qty']) ? (int)$c['qty'] : 0;
        if ($cid === '' || $qty <= 0) {
            continue;
        }
        $insert->execute([':did' => $deckId, ':cid' => $cid, ':qty' => $qty]);
    }
    $pdo->commit();
    echo json_encode(['id' => $deckId, 'name' => $name], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $deckId = 0;
    if ($_SERVER['CONTENT_TYPE'] ?? '' === 'application/json') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (is_array($input)) {
            $deckId = (int)($input['id'] ?? 0);
        }
    }
    if (!$deckId && isset($_GET['id'])) {
        $deckId = (int)$_GET['id'];
    }
    if ($deckId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'missing id']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM decks WHERE id = :id AND user_id = :uid');
    $stmt->execute([':id' => $deckId, ':uid' => $user['id']]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'deck not found']);
        exit;
    }
    echo json_encode(['deleted' => $deckId]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'method not allowed']);
