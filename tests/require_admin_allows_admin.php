<?php
declare(strict_types=1);
require __DIR__ . '/../api/_auth.php';

$pdo = new PDO('sqlite::memory:');
$pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, password_hash TEXT, is_admin TINYINT)');
$pdo->exec('CREATE TABLE sessions (user_id INTEGER, session_token TEXT, expires_at TEXT)');
$pdo->exec("INSERT INTO users (id, username, is_admin) VALUES (1, 'admin', 1)");
$pdo->exec("INSERT INTO sessions (user_id, session_token, expires_at) VALUES (1, 'admintoken', DATETIME('now', '+1 hour'))");

$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer admintoken';
$user = require_admin($pdo);
if ($user['is_admin'] === 1) {
    echo "admin ok\n";
}
