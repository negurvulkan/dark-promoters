<?php
declare(strict_types=1);
require __DIR__ . '/../src/page.php';

$userLinks = [
    ['href' => 'inventory.php', 'text' => 'Inventory', 'key' => 'inventory_button'],
    ['href' => 'market.php', 'text' => 'Market', 'key' => 'market_button'],
    ['href' => 'deckbuilder.php', 'text' => 'Deck Builder', 'key' => 'deckbuilder_button'],
    ['href' => 'matches.php', 'text' => 'Matches', 'key' => 'matches_button'],
];
render_page('index.tpl', ['user_links' => $userLinks]);
