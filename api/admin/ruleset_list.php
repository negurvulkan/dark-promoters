<?php
declare(strict_types=1);

if (!function_exists('db')) {
    require_once __DIR__ . '/../../db.php';
}
require_once __DIR__ . '/../_auth.php';

header('Content-Type: application/json');

$db = db();
require_admin($db);

$dir = realpath(__DIR__ . '/../../rulesets');
$files = [];
if ($dir !== false && is_dir($dir)) {
    foreach (scandir($dir) as $file) {
        if ($file[0] === '.') {
            continue;
        }
        $path = $dir . '/' . $file;
        if (is_file($path)) {
            $files[] = $file;
        }
    }
}

echo json_encode(['rulesets' => $files], JSON_UNESCAPED_UNICODE);
