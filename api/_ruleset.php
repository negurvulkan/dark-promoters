<?php
// Utility functions for loading and validating rulesets.

declare(strict_types=1);

function load_ruleset(string $id): array {
    $baseDir = __DIR__ . '/../rulesets/';
    $aliasPath = $baseDir . $id;
    if (is_file($aliasPath) && !str_ends_with($id, '.json')) {
        $id = trim((string)file_get_contents($aliasPath));
    }
    $jsonPath = $baseDir . $id . '.json';
    if (!is_file($jsonPath)) {
        throw new RuntimeException('ruleset not found');
    }
    $data = json_decode((string)file_get_contents($jsonPath), true);
    if (!is_array($data) || !isset($data['schema']) || ($data['id'] ?? '') !== $id) {
        throw new RuntimeException('ruleset invalid');
    }
    return ['id' => $id, 'data' => $data];
}
