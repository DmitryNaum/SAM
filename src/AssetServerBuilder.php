<?php

namespace Dmitrynaum\SAM;

/**
 * Построитель asset`ов для realtime сервера
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetServerBuilder extends AssetBuilder
{
    /**
     * Путь до приложения
     * @var string
     */
    protected $appPath;
    
    /**
     * @param string $manifestFilePath - путь до файла sam.json
     * @param string $appPath - Путь до приложения
     */
    public function __construct($manifestFilePath, $appPath)
    {
        $this->appPath = $appPath;
        
        parent::__construct($manifestFilePath);
    }

    /**
     * Получить содержимое asset`а по его имени
     * @param string $assetName
     * @return string
     * @throws \Exception
     */
    public function getAssetContentByName($assetName)
    {
        $manifest = $this->manifest();
        $assets   = array_merge($manifest->getCssAssets(), $manifest->getJsAssets());
        
        if (!isset($assets[$assetName])) {
            throw new \Exception('Asset not found', 404);
        }
                
        $assetsFiles = $this->getFilesPaths($assets);
        $assetData   = $this->readFiles($assetsFiles[$assetName]);
        
        return $assetData;
    }
}
