<?php
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
    $configContent = "<?php
return [
    'db_host' => '" . addslashes($host) . "',
    'db_port' => '" . addslashes($port) . "',
    'db_name' => '" . addslashes($name) . "',
    'db_user' => '" . addslashes($user) . "',
    'db_pass' => '" . addslashes($pass) . "',
];
";
    file_put_contents(dirname(__DIR__) . '/config.php', $configContent);

    try {
        $dsn = "mysql:host={$host};port={$port}";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$name}`");
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
    <p><a href="index.html">Go to application</a></p>
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
