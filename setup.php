<?php
// Setup script to configure database and run migrations via CLI or web form

function performSetup(string $host, string $port, string $name, string $user, string $pass): string {
    $out = '';
    $configContent = "<?php\nreturn [\n    'db_host' => '" . addslashes($host) . "',\n    'db_port' => '" . addslashes($port) . "',\n    'db_name' => '" . addslashes($name) . "',\n    'db_user' => '" . addslashes($user) . "',\n    'db_pass' => '" . addslashes($pass) . "',\n];\n";
    file_put_contents(__DIR__ . '/config.php', $configContent);
    $out .= "config.php written\n";

    try {
        $serverDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $pdo = new PDO($serverDsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $out .= "Database '{$name}' ready\n";

        $migrationsDir = __DIR__ . '/migrations';
        $files = glob($migrationsDir . '/*.sql');
        natsort($files);
        foreach ($files as $file) {
            $sql = file_get_contents($file);
            $out .= "Applying " . basename($file) . "...";
            $pdo->exec($sql);
            $out .= "done\n";
        }
        $out .= "Setup complete.\n";
    } catch (PDOException $e) {
        $out .= "Connection failed: " . $e->getMessage() . "\n";
    }
    return $out;
}

if (php_sapi_name() !== 'cli') {
    $default = [
        'db_host' => 'localhost',
        'db_port' => '3306',
        'db_name' => 'dark_promoters',
        'db_user' => 'root',
    ];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $host = $_POST['db_host'] ?? $default['db_host'];
        $port = $_POST['db_port'] ?? $default['db_port'];
        $name = $_POST['db_name'] ?? $default['db_name'];
        $user = $_POST['db_user'] ?? $default['db_user'];
        $pass = $_POST['db_pass'] ?? '';
        $log = performSetup($host, $port, $name, $user, $pass);
        echo '<!DOCTYPE html><html><body><pre>' . htmlspecialchars($log, ENT_QUOTES) . '</pre></body></html>';
    } else {
        echo '<!DOCTYPE html><html><body><form method="post">'
            . '<label>DB host <input name="db_host" value="' . htmlspecialchars($default['db_host'], ENT_QUOTES) . '"></label><br>'
            . '<label>DB port <input name="db_port" value="' . htmlspecialchars($default['db_port'], ENT_QUOTES) . '"></label><br>'
            . '<label>DB name <input name="db_name" value="' . htmlspecialchars($default['db_name'], ENT_QUOTES) . '"></label><br>'
            . '<label>DB user <input name="db_user" value="' . htmlspecialchars($default['db_user'], ENT_QUOTES) . '"></label><br>'
            . '<label>DB pass <input type="password" name="db_pass"></label><br>'
            . '<button type="submit">Run Setup</button>'
            . '</form></body></html>';
    }
    return;
}

function prompt(string $prompt, string $default = ''): string {
    if ($default !== '') {
        $prompt .= " [$default]";
    }
    $prompt .= ': ';
    if (function_exists('readline')) {
        $input = readline($prompt);
    } else {
        echo $prompt;
        $input = fgets(STDIN);
    }
    $input = $input === false ? '' : trim($input);
    return $input === '' ? $default : $input;
}

$host = prompt('DB host', 'localhost');
$port = prompt('DB port', '3306');
$name = prompt('DB name', 'dark_promoters');
$user = prompt('DB user', 'root');
$pass = prompt('DB password');

echo performSetup($host, $port, $name, $user, $pass);
