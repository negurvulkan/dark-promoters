<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

function current_user(PDO $pdo): ?array {
    $token = $_COOKIE['session_token'] ?? '';
    if ($token === '') {
        return null;
    }
    $stmt = $pdo->prepare('SELECT u.id, u.username, u.is_admin FROM sessions s JOIN users u ON s.user_id = u.id WHERE s.session_token = ? AND s.expires_at > NOW()');
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        return null;
    }
    return [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'is_admin' => (int)$user['is_admin'],
    ];
}

function load_translations(string $locale): array {
    $baseFile = __DIR__ . '/../i18n/ui.en.json';
    $base = json_decode(@file_get_contents($baseFile), true) ?: [];
    if ($locale !== 'en') {
        $localeFile = __DIR__ . '/../i18n/ui.' . $locale . '.json';
        if (is_file($localeFile)) {
            $extra = json_decode(@file_get_contents($localeFile), true) ?: [];
            $base = array_merge($base, $extra);
        }
    }
    return $base;
}

function render_page(string $template, array $vars = []): void {
    $smarty = require __DIR__ . '/bootstrap.php';
    $pdo = db();
    $locale = $_COOKIE['locale'] ?? 'en';
    $translations = load_translations($locale);
    $user = current_user($pdo);
    $smarty->assign('i18n', $translations);
    $smarty->assign('user', $user);
    $smarty->assign('is_logged_in', $user !== null);
    $showAdmin = $user && $user['is_admin'] === 1;
    $smarty->assign('show_admin', $showAdmin);
    $nav_links = [];
    if ($user) {
        $nav_links = [
            ['href' => '../public/inventory.php', 'key' => 'nav_inventory'],
            ['href' => '../public/market.php', 'key' => 'nav_market'],
            ['href' => '../public/deckbuilder.php', 'key' => 'nav_deckbuilder'],
            ['href' => '../public/matches.php', 'key' => 'nav_matches'],
        ];
        if ($showAdmin) {
            $nav_links[] = ['href' => '../public/admin.php', 'key' => 'nav_admin'];
        }
    }
    $smarty->assign('nav_links', $nav_links);
    foreach ($vars as $k => $v) {
        $smarty->assign($k, $v);
    }
    $smarty->display($template);
}
