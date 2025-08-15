<?php
declare(strict_types=1);

class TestPDO extends PDO {
    public function prepare(string $statement, array $options = []): PDOStatement|false {
        $statement = str_replace('FOR UPDATE', '', $statement);
        return parent::prepare($statement, $options);
    }
}

class MockPhpStream {
    public $context;
    private static $input = '';
    private $index = 0;
    public static function setInput(string $input): void { self::$input = $input; }
    public function stream_open($path, $mode, $options, &$opened_path): bool { $this->index = 0; return true; }
    public function stream_read($count) { $ret = substr(self::$input, $this->index, $count); $this->index += strlen($ret); return $ret; }
    public function stream_eof(): bool { return $this->index >= strlen(self::$input); }
    public function stream_stat() { return []; }
}

$pdo = new TestPDO('sqlite::memory:');
$pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, is_admin TINYINT)');
$pdo->exec('CREATE TABLE sessions (user_id INTEGER, session_token TEXT, expires_at TEXT)');
$pdo->exec('CREATE TABLE matches (id INTEGER PRIMARY KEY, creator_id INT, status TEXT)');
$pdo->exec('CREATE TABLE match_players (match_id INT, user_id INT, username TEXT, is_ai TINYINT)');
$pdo->exec('CREATE TABLE games (id INTEGER PRIMARY KEY, host_user_id INT, state_json TEXT, version INT, ruleset_id TEXT, rules_json_snapshot TEXT, match_id INT)');
$pdo->exec('CREATE TABLE game_players (game_id INT, user_id INT, username TEXT, is_ai TINYINT)');
$pdo->exec("INSERT INTO users (id, username, is_admin) VALUES (1, 'host', 0), (2, 'guest', 0)");
$pdo->exec("INSERT INTO sessions (user_id, session_token, expires_at) VALUES (1, 'token', DATETIME('now', '+1 hour'))");
$pdo->exec("INSERT INTO matches (id, creator_id, status) VALUES (1, 1, 'waiting')");
$pdo->exec("INSERT INTO match_players (match_id, user_id, username, is_ai) VALUES (1, 1, 'host', 0), (1, 2, 'guest', 0)");

stream_wrapper_unregister('php');
stream_wrapper_register('php', MockPhpStream::class);
MockPhpStream::setInput(json_encode(['match_id' => 1]));

$realDb = __DIR__ . '/../db.php';
$bakDb = __DIR__ . '/../db.php.bak';
rename($realDb, $bakDb);
file_put_contents($realDb, "<?php\nfunction db(): PDO { global \$pdo; return \$pdo; }\n");

$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer token';
ob_start();
require __DIR__ . '/../api/matches_start.php';
$out = ob_get_clean();

unlink($realDb);
rename($bakDb, $realDb);
stream_wrapper_restore('php');

$data = json_decode($out, true);
$players = (int)$pdo->query('SELECT COUNT(*) FROM game_players')->fetchColumn();

echo 'game_id:' . ($data['game_id'] ?? 0) . ', players:' . $players . "\n";
