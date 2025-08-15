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

/**
 * Let the simple AI perform a turn.
 * Chooses a random playable card or ends the phase and persists state.
 */
function apply_ai_turn(PDO $pdo, int $gameId, array $rules): ?array {
    $stmt = $pdo->prepare('SELECT state_json, version FROM games WHERE id = ?');
    $stmt->execute([$gameId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return null;
    }

    $state = json_decode($row['state_json'], true);
    $version = (int)$row['version'];
    if (empty($state['ai_enabled'])) {
        return $state;
    }

    $phase = $state['phase'] ?? '';
    $aiHand = $state['ai_hand'] ?? [];
    $allowedMap = [
        'finance1' => ['sponsor'],
        'location' => ['location'],
        'booking1' => ['act'],
        'marketing1' => ['marketing'],
        'sabotage' => ['sabotage'],
        'finance2' => ['sponsor'],
        'booking2' => ['act'],
        'marketing2' => ['marketing'],
        'event' => [],
        'game_over' => [],
    ];
    $allowed = $allowedMap[$phase] ?? [];

    $playable = [];
    foreach ($aiHand as $idx => $card) {
        $type = is_array($card) ? ($card['type'] ?? '') : '';
        if (in_array($type, $allowed, true)) {
            $playable[$idx] = $card;
        }
    }

    if ($playable) {
        $idx = array_rand($playable);
        $card = $playable[$idx];
        $cardId = is_array($card) ? ($card['id'] ?? null) : $card;
        $state['hand'] = $aiHand;
        $state = apply_action($state, ['type' => 'play', 'card' => $cardId], $rules);
        $state['ai_hand'] = $state['hand'];
        unset($state['hand']);
        if (!empty($state['ai_deck'])) {
            $draw = array_shift($state['ai_deck']);
            if ($draw !== null) {
                $state['ai_hand'][] = $draw;
            }
        }
        $state['log'][] = "ai play {$cardId}";
    } else {
        $state['hand'] = $aiHand;
        $state = apply_action($state, ['type' => 'next_phase'], $rules);
        $state['ai_hand'] = $state['hand'];
        unset($state['hand']);
        $state['log'][] = 'ai next_phase';
    }

    $state['version'] = $version + 1;
    update_game_state($pdo, $gameId, $version, $state);
    return $state;
}
