<?php
declare(strict_types=1);

function compute_score(array &$state, array $rules): void {
    $mode = $state['mode'] ?? '';
    $modeRules = $rules['modes'][$mode] ?? [];
    if (empty($modeRules)) {
        throw new RuntimeException('unknown mode');
    }
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

$state = ['mode' => 'nonexistent'];
$rules = ['modes' => ['club' => ['audienceBase' => 100, 'ticketPriceDefault' => 10]]];

try {
    compute_score($state, $rules);
    echo 'score: ' . ($state['audience'] ?? 'missing') . "\n";
} catch (RuntimeException $e) {
    echo 'error: ' . $e->getMessage() . "\n";
}
