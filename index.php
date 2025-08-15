<?php
declare(strict_types=1);
require __DIR__ . '/src/page.php';

$userLinks = [
    ['href' => 'public/inventory.php', 'text' => 'Inventory', 'key' => 'inventory_button'],
    ['href' => 'public/market.php', 'text' => 'Market', 'key' => 'market_button'],
    ['href' => 'public/deckbuilder.php', 'text' => 'Deck Builder', 'key' => 'deckbuilder_button'],
    ['href' => 'public/matches.php', 'text' => 'Matches', 'key' => 'matches_button'],
];
render_page('index.tpl', ['user_links' => $userLinks]);
