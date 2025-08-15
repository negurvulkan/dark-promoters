<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Smarty\Smarty;

$smarty = new Smarty();

$templateDir = __DIR__ . '/../templates';
$compileDir = __DIR__ . '/../templates_c';

if (!is_dir($templateDir)) {
    mkdir($templateDir, 0777, true);
}
if (!is_dir($compileDir)) {
    mkdir($compileDir, 0777, true);
}

$smarty->setTemplateDir($templateDir);
$smarty->setCompileDir($compileDir);

return $smarty;
