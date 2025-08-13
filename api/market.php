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

function load_packs(): array {
  $path = realpath(__DIR__ . '/../packs.json');
  if ($path === false || !is_file($path)) {
    return [];
  }
  $data = json_decode(file_get_contents($path), true);
  return is_array($data) ? $data : [];
}

function load_cards_by_type(): array {
  $baseDir = realpath(__DIR__ . '/../cards');
  $cards = [];
  if ($baseDir === false || !is_dir($baseDir)) {
    return $cards;
  }
  $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
  foreach ($iter as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'json') {
      continue;
    }
    $data = json_decode(file_get_contents($file->getPathname()), true);
    if (!is_array($data) || !isset($data['id']) || !isset($data['type'])) {
      continue;
    }
    $type = $data['type'];
    $cards[$type] = $cards[$type] ?? [];
    $cards[$type][] = $data['id'];
  }
  return $cards;
}

$pdo = db();
$user = require_session($pdo);
$packs = load_packs();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $stmt = $pdo->prepare('SELECT points FROM users WHERE id = ?');
  $stmt->execute([$user['id']]);
  $points = (int)$stmt->fetchColumn();
  echo json_encode(['points' => $points, 'packs' => $packs], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid json']);
    exit;
  }
  $packId = trim((string)($input['pack_id'] ?? ''));
  $pack = null;
  foreach ($packs as $p) {
    if (($p['id'] ?? '') === $packId) {
      $pack = $p;
      break;
    }
  }
  if ($pack === null) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid pack']);
    exit;
  }
  $cost = (int)($pack['cost'] ?? 0);
  $pdo->beginTransaction();
  $stmt = $pdo->prepare('SELECT points FROM users WHERE id = ? FOR UPDATE');
  $stmt->execute([$user['id']]);
  $points = (int)$stmt->fetchColumn();
  if ($points < $cost) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['error' => 'insufficient points']);
    exit;
  }
  $stmt = $pdo->prepare('UPDATE users SET points = points - :cost WHERE id = :uid');
  $stmt->execute([':cost' => $cost, ':uid' => $user['id']]);

  $cardsByType = load_cards_by_type();
  $awarded = [];
  foreach ($pack['contents'] as $item) {
    $type = $item['card_type'] ?? '';
    $qty = (int)($item['qty'] ?? 0);
    if (!isset($cardsByType[$type]) || $qty <= 0) {
      continue;
    }
    for ($i = 0; $i < $qty; $i++) {
      $choices = $cardsByType[$type];
      if (!$choices) {
        break;
      }
      $cardId = $choices[array_rand($choices)];
      $stmt = $pdo->prepare('INSERT INTO card_inventory (user_id, card_id, qty) VALUES (:uid, :cid, 1) ON CONFLICT (user_id, card_id) DO UPDATE SET qty = card_inventory.qty + 1');
      $stmt->execute([':uid' => $user['id'], ':cid' => $cardId]);
      $awarded[] = $cardId;
    }
  }
  $pdo->commit();
  $newPoints = $points - $cost;
  echo json_encode(['points' => $newPoints, 'awarded' => $awarded], JSON_UNESCAPED_UNICODE);
  exit;
}

http_response_code(405);
echo json_encode(['error' => 'method not allowed']);

