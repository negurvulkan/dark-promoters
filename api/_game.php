<?php
// Shared game creation helper.

declare(strict_types=1);

require_once __DIR__ . '/_ruleset.php';

/**
 * Create a new game row and return identifiers.
 * Caller must handle transactions.
 */
function create_game(PDO $pdo, int $host_user_id, string $ruleset_id, array $initial_state, ?int $match_id = null): array {
    $loaded = load_ruleset($ruleset_id);
    $ruleset_id = $loaded['id'];
    $rules_snapshot = $loaded['data'];

    $initial_state['version'] = $initial_state['version'] ?? 0;

    $stmt = $pdo->prepare('INSERT INTO games (host_user_id, state_json, version, ruleset_id, rules_json_snapshot, match_id) VALUES (:host, :state, 0, :ruleset, :rules, :match) RETURNING id');
    $stmt->execute([
        ':host' => $host_user_id,
        ':state' => json_encode($initial_state, JSON_UNESCAPED_UNICODE),
        ':ruleset' => $ruleset_id,
        ':rules' => json_encode($rules_snapshot, JSON_UNESCAPED_UNICODE),
        ':match' => $match_id,
    ]);
    $game_id = (int)$stmt->fetchColumn();

    return ['game_id' => $game_id, 'state' => $initial_state];
}
