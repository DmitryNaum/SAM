<?php

namespace Dmitrynaum\SAM;

/**
 * Description of ServerBuilder
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
        
        $asset = $assets[$assetName];
        
        $assetFiles = [];
        foreach ($asset as $assetFile) {
            $assetFiles[] = realpath("{$this->appPath}/{$assetFile}");
        }
        $assetData = $this->readFiles($assetFiles);
        
        return $assetData;
    }
}
