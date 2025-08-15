<?php
// Simple AI helper functions.

declare(strict_types=1);

/**
 * Draw a card for the AI from its deck into its hand.
 */
function draw_ai_hand(array &$state): void {
    if (empty($state['ai_deck']) || !is_array($state['ai_deck'])) {
        return;
    }
    $card = array_shift($state['ai_deck']);
    if ($card !== null) {
        $state['ai_hand'][] = $card;
    }
}

/**
 * Choose a playable card id from the AI hand for the current phase.
 * Returns null if no card can be played.
 */
function choose_ai_card(array $state): ?string {
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
    foreach ($aiHand as $card) {
        $type = is_array($card) ? ($card['type'] ?? '') : '';
        if (in_array($type, $allowed, true)) {
            $playable[] = is_array($card) ? ($card['id'] ?? null) : $card;
        }
    }
    if (!$playable) {
        return null;
    }
    return $playable[array_rand($playable)];
}

/**
 * Advance the AI to the next phase.
 */
function next_ai_phase(array &$state, array $rules): void {
    $state['hand'] = $state['ai_hand'] ?? [];
    $state = apply_action($state, ['type' => 'next_phase'], $rules);
    $state['ai_hand'] = $state['hand'];
    unset($state['hand']);
    $state['log'][] = 'ai next_phase';
}
