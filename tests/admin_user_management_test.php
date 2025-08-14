<?php
declare(strict_types=1);

$pdo = new PDO('sqlite::memory:');
$pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, is_admin TINYINT, points INT)');
$pdo->exec('CREATE TABLE sessions (user_id INTEGER, session_token TEXT, expires_at TEXT)');
$pdo->exec('CREATE TABLE point_log (user_id INTEGER, delta INT, reason TEXT, created_at TEXT)');
$pdo->exec("INSERT INTO users (id, username, is_admin, points) VALUES (1, 'admin', 1, 1000), (2, 'user', 0, 100)");
$pdo->exec("INSERT INTO sessions (user_id, session_token, expires_at) VALUES (1, 'admintoken', DATETIME('now', '+1 hour'))");

function db() {
    global $pdo;
    return $pdo;
}

$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer admintoken';

ob_start();
require __DIR__ . '/../api/admin/user_list.php';
$list = json_decode(ob_get_clean(), true);

$_POST = ['id' => 2, 'username' => 'user2', 'is_admin' => 1];
ob_start();
require __DIR__ . '/../api/admin/user_update.php';
ob_get_clean();

$_POST = ['id' => 2, 'delta' => 50];
ob_start();
require __DIR__ . '/../api/admin/user_points.php';
ob_get_clean();
$pointsAfter = (int)$pdo->query('SELECT points FROM users WHERE id = 2')->fetchColumn();

$_POST = ['id' => 2];
ob_start();
require __DIR__ . '/../api/admin/user_delete.php';
ob_get_clean();

$usersAfter = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

echo 'initial_users: ' . count($list['users']) . "\n";
echo 'points_after_add: ' . $pointsAfter . "\n";
echo 'users_after_delete: ' . $usersAfter . "\n";
