<?php
// Interactive setup script to configure database and run migrations

function prompt(string $prompt, string $default = ''): string {
    if ($default !== '') {
        $prompt .= " [{$default}]";
    }
    $prompt .= ': ';
    $input = readline($prompt);
    if ($input === false || $input === '') {
        return $default;
    }
    return $input;
}

// Collect DB credentials
$host = prompt('DB host', 'localhost');
$port = prompt('DB port', '3306');
$name = prompt('DB name', 'dark_promoters');
$user = prompt('DB user', 'root');
$pass = prompt('DB password');

// Write config.php
$configContent = "<?php\nreturn [\n    'db_host' => '" . addslashes($host) . "',\n    'db_port' => '" . addslashes($port) . "',\n    'db_name' => '" . addslashes($name) . "',\n    'db_user' => '" . addslashes($user) . "',\n    'db_pass' => '" . addslashes($pass) . "',\n];\n";
file_put_contents(__DIR__ . '/config.php', $configContent);
echo "config.php written\n";

// Connect to MySQL
$dsn = "mysql:host={$host};port={$port}";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    fwrite(STDERR, "Connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

// Create database if not exists
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$name}`");

echo "Database '{$name}' ready\n";

// Run migrations
$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.sql');
natsort($files);
foreach ($files as $file) {
    $sql = file_get_contents($file);
    echo "Applying " . basename($file) . "...";
    $pdo->exec($sql);
    echo "done\n";
}

echo "Setup complete.\n";

