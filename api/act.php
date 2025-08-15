<?php
// Apply an action to game state with optimistic locking.

declare(strict_types=1);

$smarty = require __DIR__ . '/../src/bootstrap.php';

require_once __DIR__ . '/update_game.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid json']);
    exit;
}

$game_id = isset($input['game_id']) ? (int)$input['game_id'] : 0;
$expected_version = isset($input['expected_version']) ? (int)$input['expected_version'] : -1;
$action = $input['action'] ?? null;
if ($game_id <= 0 || $expected_version < 0 || !is_array($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'missing fields']);
    exit;
}

function allowed_types_for_phase(string $phase): array {
    return [
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
    ][$phase] ?? [];
}

function next_phase(string $phase): string {
    $order = ['finance1','location','booking1','marketing1','sabotage','finance2','booking2','marketing2','event','game_over'];
    $idx = array_search($phase, $order, true);
    return $order[$idx + 1] ?? $phase;
}

function apply_card_effect(array &$state, array $card, array $rules): void {
    $type = $card['type'] ?? '';
    if ($type === 'sponsor') {
        $slots = (int)($card['min_slots'] ?? 0);
        $payout = (float)($card['payout_per_slot'] ?? 0) * $slots;
        $state['sponsor_payout'] = ($state['sponsor_payout'] ?? 0) + $payout;
    } elseif ($type === 'location') {
        if (isset($state['location'])) {
            throw new RuntimeException('location already chosen');
        }
        $state['location'] = $card['id'] ?? '';
        $state['capacity'] = (int)($card['capacity'] ?? 0);
        $state['spend'] = ($state['spend'] ?? 0) + (float)($card['rent'] ?? 0);
    } elseif ($type === 'act') {
        $state['acts'] = $state['acts'] ?? [];
        foreach ($state['acts'] as $a) {
            if (($a['id'] ?? null) === ($card['id'] ?? null)) {
                throw new RuntimeException('act already booked');
            }
        }
        $state['acts'][] = $card;
        $state['audience_pct_acts'] = ($state['audience_pct_acts'] ?? 0) + (float)($card['audience_pct'] ?? 0);
        $state['spend'] = ($state['spend'] ?? 0) + (float)($card['cost'] ?? 0);
    } elseif ($type === 'marketing') {
        $state['marketing'] = $state['marketing'] ?? [];
        $state['marketing'][] = $card;
        $state['audience_pct_marketing'] = ($state['audience_pct_marketing'] ?? 0) + (float)($card['audience_pct'] ?? 0);
        $state['spend'] = ($state['spend'] ?? 0) + (float)($card['cost'] ?? 0);
    } elseif ($type === 'sabotage') {
        $text = $card['text']['en'] ?? '';
        if (preg_match('/-\s*(\d+)\s*audience/i', $text, $m)) {
            $state['audience_penalty'] = ($state['audience_penalty'] ?? 0) + (int)$m[1];
        }
    }
}

function compute_score(array &$state, array $rules): void {
    $mode = $state['mode'] ?? '';
    $modeRules = $rules['modes'][$mode] ?? [];
    $base = (float)($modeRules['audienceBase'] ?? 0);
    $ticket = (float)($state['ticket_price'] ?? ($modeRules['ticketPriceDefault'] ?? 0));
    $audience = $base * (1 + ($state['audience_pct_acts'] ?? 0)) * (1 + ($state['audience_pct_marketing'] ?? 0));
    $audience -= $state['audience_penalty'] ?? 0;
    if (isset($state['capacity'])) {
        $audience = min($audience, (float)$state['capacity']);
    }
    $profit = ($ticket * $audience) + ($state['sponsor_payout'] ?? 0) - ($state['spend'] ?? 0);
    $state['audience'] = $audience;
    $state['profit'] = $profit;
}

function play_card(array &$state, string $cardId, array $rules): void {
    $hand = $state['hand'] ?? [];
    $card = null;
    $index = null;
    foreach ($hand as $i => $c) {
        $id = is_array($c) ? ($c['id'] ?? null) : $c;
        if ($id === $cardId) {
            $card = is_array($c) ? $c : ['id' => $id];
            $index = $i;
            break;
        }
    }
    if ($card === null) {
        throw new RuntimeException('card not in hand');
    }
    $phase = $state['phase'] ?? '';
    $allowed = allowed_types_for_phase($phase);
    if (!in_array($card['type'] ?? '', $allowed, true)) {
        throw new RuntimeException('card not allowed in phase');
    }
    array_splice($hand, $index, 1);
    $state['hand'] = array_values($hand);
    $state['table'] = $state['table'] ?? [];
    $state['table'][] = $card;
    apply_card_effect($state, $card, $rules);
    $state['log'][] = "play {$cardId}";
}

function end_phase(array &$state): void {
    $state['phase'] = next_phase($state['phase'] ?? '');
    $state['log'][] = "phase {$state['phase']}";
}

function check_win(array &$state, array $rules): void {
    if (($state['phase'] ?? '') === 'event') {
        compute_score($state, $rules);
        $state['winner'] = $state['winner'] ?? 'player';
    } elseif (($state['phase'] ?? '') === 'game_over') {
        compute_score($state, $rules);
        $reward = $rules['global']['winReward'] ?? 0;
        $state['log'][] = "game over profit {$state['profit']} audience {$state['audience']} reward {$reward}";
    }
}

function apply_action(array $state, array $action, array $rules): array {
    $state['log'] = $state['log'] ?? [];
    $type = $action['type'] ?? '';
    if ($type === 'play') {
        $cardId = $action['card'] ?? null;
        if (!$cardId) {
            throw new RuntimeException('missing card');
        }
        play_card($state, $cardId, $rules);
    } elseif ($type === 'next_phase') {
        end_phase($state);
    } else {
        $state['log'][] = ['unknown_action' => $action];
    }
    check_win($state, $rules);
    return $state;
}

$pdo = db();
try {
    $stmt = $pdo->prepare('SELECT state_json, version, rules_json_snapshot FROM games WHERE id = ?');
    $stmt->execute([$game_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'game not found']);
        exit;
    }
    if ((int)$row['version'] !== $expected_version) {
        http_response_code(409);
        echo json_encode(['error' => 'version mismatch']);
        exit;
    }
    $state = json_decode($row['state_json'], true);
    $rules = json_decode($row['rules_json_snapshot'], true);
    $new_state = apply_action($state, $action, $rules);
    $new_state['version'] = $state['version'] + 1;
    update_game_state($pdo, $game_id, $expected_version, $new_state);
    echo json_encode(['state' => $new_state], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $e) {
    if ($e->getMessage() === 'Version mismatch') {
        http_response_code(409);
        echo json_encode(['error' => 'version mismatch']);
    } elseif ($e->getMessage() === 'Game not found') {
        http_response_code(404);
        echo json_encode(['error' => 'game not found']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'server error']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server error']);
}
