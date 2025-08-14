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
$port = prompt('DB port', '5432');
$name = prompt('DB name', 'dark_promoters');
$user = prompt('DB user', 'postgres');
$pass = prompt('DB password');

// Write config.php
$configContent = "<?php\nreturn [\n    'db_host' => '" . addslashes($host) . "',\n    'db_port' => '" . addslashes($port) . "',\n    'db_name' => '" . addslashes($name) . "',\n    'db_user' => '" . addslashes($user) . "',\n    'db_pass' => '" . addslashes($pass) . "',\n];\n";
file_put_contents(__DIR__ . '/config.php', $configContent);
echo "config.php written\n";

// Load config and connect to PostgreSQL server
$cfg = require __DIR__ . '/config.php';
$dsnServer = "pgsql:host={$cfg['db_host']};port={$cfg['db_port']};dbname=postgres";
try {
    $pdo = new PDO($dsnServer, $cfg['db_user'], $cfg['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    fwrite(STDERR, "Connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

// Create database if not exists
try {
    $pdo->exec('CREATE DATABASE "' . $cfg['db_name'] . '"');
} catch (PDOException $e) {
    if ($e->getCode() !== '42P04') {
        throw $e;
    }
}

// Connect to target database
$dsn = "pgsql:host={$cfg['db_host']};port={$cfg['db_port']};dbname={$cfg['db_name']}";
$pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "Database '{$cfg['db_name']}' ready\n";

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
