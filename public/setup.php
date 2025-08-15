<?php
$smarty = require __DIR__ . '/../src/bootstrap.php';

// Web-based setup script to configure database and run migrations
$default = [
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_name' => 'dark_promoters',
    'db_user' => 'root',
];

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['db_host'] ?? $default['db_host'];
    $port = $_POST['db_port'] ?? $default['db_port'];
    $name = $_POST['db_name'] ?? $default['db_name'];
    $user = $_POST['db_user'] ?? $default['db_user'];
    $pass = $_POST['db_pass'] ?? '';

    // Write config.php
    $configContent = "<?php\nreturn [\n    'db_host' => '" . addslashes($host) . "',\n    'db_port' => '" . addslashes($port) . "',\n    'db_name' => '" . addslashes($name) . "',\n    'db_user' => '" . addslashes($user) . "',\n    'db_pass' => '" . addslashes($pass) . "',\n];\n";
    $configPath = dirname(__DIR__) . '/config.php';
    file_put_contents($configPath, $configContent);

    try {
        $cfg = require $configPath;

        $serverDsn = "mysql:host={$cfg['db_host']};port={$cfg['db_port']};charset=utf8mb4";
        $pdo = new PDO($serverDsn, $cfg['db_user'], $cfg['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$cfg['db_name'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $dsn = "mysql:host={$cfg['db_host']};port={$cfg['db_port']};dbname={$cfg['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $migrationsDir = dirname(__DIR__) . '/migrations';
        $files = glob($migrationsDir . '/*.sql');
        natsort($files);
        foreach ($files as $file) {
            $sql = file_get_contents($file);
            $pdo->exec($sql);
        }
        $success = true;
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Dark Promoters Setup</title>
</head>
<body>
<?php if ($success): ?>
    <h1>Setup Complete</h1>
    <p>Database configured and migrations applied.</p>
    <p><a href="index.php">Go to application</a></p>
<?php else: ?>
    <h1>Dark Promoters Setup</h1>
    <?php if ($error): ?>
        <p style="color:red;">Error: <?= htmlspecialchars($error, ENT_QUOTES) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>DB host <input name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? $default['db_host'], ENT_QUOTES) ?>"></label><br>
        <label>DB port <input name="db_port" value="<?= htmlspecialchars($_POST['db_port'] ?? $default['db_port'], ENT_QUOTES) ?>"></label><br>
        <label>DB name <input name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? $default['db_name'], ENT_QUOTES) ?>"></label><br>
        <label>DB user <input name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? $default['db_user'], ENT_QUOTES) ?>"></label><br>
        <label>DB pass <input type="password" name="db_pass"></label><br>
        <button type="submit">Run Setup</button>
    </form>
<?php endif; ?>
</body>
</html>
