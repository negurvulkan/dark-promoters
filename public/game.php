<?php
declare(strict_types=1);
require __DIR__ . '/../src/page.php';

$vs_ai = isset($_GET['vs_ai']) ? '1' : '0';
render_page('game.tpl', ['vs_ai' => $vs_ai]);
