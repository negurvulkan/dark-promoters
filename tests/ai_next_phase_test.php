<?php
declare(strict_types=1);

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

function apply_action(array $state, array $action, array $rules): array {
    $state['log'] = $state['log'] ?? [];
    if (($action['type'] ?? '') === 'next_phase') {
        $state['phase'] = next_phase($state['phase'] ?? '');
        $state['log'][] = "phase {$state['phase']}";
    }
    return $state;
}

function update_game_state(PDO $pdo, int $game_id, int $expected_version, array $new_state): void {
    $stmt = $pdo->prepare('UPDATE games SET state_json = :state, version = version + 1 WHERE id = :id');
    $stmt->execute([':state' => json_encode($new_state, JSON_UNESCAPED_UNICODE), ':id' => $game_id]);
}

require __DIR__ . '/../api/_game.php';

$pdo = new PDO('sqlite::memory:');
$pdo->exec('CREATE TABLE games (id INTEGER PRIMARY KEY, state_json TEXT, version INTEGER)');
$state = [
    'ai_enabled' => true,
    'phase' => 'booking1',
    'ai_hand' => [ ['id' => 's1', 'type' => 'sponsor'] ],
    'ai_deck' => [],
    'log' => [],
];
$pdo->prepare('INSERT INTO games (id, state_json, version) VALUES (1, :state, 0)')
    ->execute([':state' => json_encode($state, JSON_UNESCAPED_UNICODE)]);

apply_ai_turn($pdo, 1, []);
$row = $pdo->query('SELECT state_json FROM games WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
$updated = json_decode($row['state_json'], true);

echo 'phase:' . $updated['phase'] . ', log:' . end($updated['log']) . "\n";
