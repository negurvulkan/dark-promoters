<?php
/**
 * Update game state with optimistic locking.
 */
function update_game_state(PDO $pdo, int $game_id, int $expected_version, array $new_state): void {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare('SELECT version FROM games WHERE id = ? FOR UPDATE');
  $stmt->execute([$game_id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    $pdo->rollBack();
    throw new RuntimeException('Game not found');
  }
  if ((int)$row['version'] !== $expected_version) {
    $pdo->rollBack();
    throw new RuntimeException('Version mismatch');
  }

  $update = $pdo->prepare('UPDATE games SET state_json = :state, version = version + 1 WHERE id = :id');
  $update->execute([
    ':state' => json_encode($new_state, JSON_UNESCAPED_UNICODE),
    ':id' => $game_id,
  ]);

  $pdo->commit();
}
