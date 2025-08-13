<?php
// Load all card JSON files recursively and validate minimal schema.

declare(strict_types=1);

header('Content-Type: application/json');

$baseDir = realpath(__DIR__ . '/../cards');
$cards = [];
$warnings = [];

if ($baseDir === false || !is_dir($baseDir)) {
    echo json_encode(['cards' => [], 'warnings' => ['cards directory missing']], JSON_UNESCAPED_UNICODE);
    exit;
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'json') {
        continue;
    }
    $path = $file->getPathname();
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    if (!is_array($data)) {
        $warnings[] = "Invalid JSON: $path";
        continue;
    }
    $missing = [];
    foreach (['schema', 'type', 'id'] as $field) {
        if (!array_key_exists($field, $data)) {
            $missing[] = $field;
        }
    }
    if ($missing) {
        $warnings[] = sprintf('Card %s missing fields: %s', $path, implode(', ', $missing));
        continue;
    }

    if (!isset($data['name']) && !isset($data['name_key'])) {
        $warnings[] = sprintf('Card %s missing fields: name or name_key', $path);
        continue;
    }
    // Validate i18n fields for strings like name
    if (isset($data['name'])) {
        if (is_array($data['name'])) {
            if (!isset($data['name']['en']) || !isset($data['name']['de'])) {
                $warnings[] = sprintf('Card %s has invalid i18n name', $path);
                continue;
            }
        } elseif (!is_string($data['name'])) {
            $warnings[] = sprintf('Card %s has invalid name', $path);
            continue;
        }
    } elseif (isset($data['name_key']) && !is_string($data['name_key'])) {
        $warnings[] = sprintf('Card %s has invalid name_key', $path);
        continue;
    }
    if (isset($data['style']) && !is_array($data['style'])) {
        $warnings[] = sprintf('Card %s has invalid style block', $path);
        continue;
    }
    $cards[] = $data;
}

echo json_encode(['cards' => $cards, 'warnings' => $warnings], JSON_UNESCAPED_UNICODE);
