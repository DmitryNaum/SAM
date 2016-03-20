<?php
// Сервер asset`ов

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

$projectDir   = getenv('projectDir');
$manifestPath = getenv('manifestPath');

$assetBuilder = new Dmitrynaum\SAM\AssetServerBuilder($manifestPath, $projectDir);
$assetName    = isset($_GET['asset']) ? $_GET['asset'] : null;

try{
    $content = $assetBuilder->getAssetContentByName($assetName);
} catch (Exception $e) {
    if ($e->getCode() == 404) {
        header("HTTP/1.0 404 Not Found");
    } else {
        throw $e;
    }
}

echo $content;
