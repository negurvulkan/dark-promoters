<?php
declare(strict_types=1);

$smarty = require __DIR__ . '/../src/bootstrap.php';

// Web-based setup script to configure database and run migrations
$default = [
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_name' => 'dark_promoters',
    'db_user' => 'root',
];

$values = [
    'db_host' => $_POST['db_host'] ?? $default['db_host'],
    'db_port' => $_POST['db_port'] ?? $default['db_port'],
    'db_name' => $_POST['db_name'] ?? $default['db_name'],
    'db_user' => $_POST['db_user'] ?? $default['db_user'],
];

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['db_pass'] ?? '';

    // Write config.php
    $configContent = "<?php\nreturn [\n    'db_host' => '" . addslashes($values['db_host']) . "',\n    'db_port' => '" . addslashes($values['db_port']) . "',\n    'db_name' => '" . addslashes($values['db_name']) . "',\n    'db_user' => '" . addslashes($values['db_user']) . "',\n    'db_pass' => '" . addslashes($pass) . "',\n];\n";
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

$smarty->assign('success', $success);
$smarty->assign('error', $error);
$smarty->assign('values', $values);
$smarty->display('setup.tpl');

