<?php

namespace Dmitrynaum\SAM\Component;

/**
 * Класс для работы с картой Asset`ов
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetMap
{
    /**
     * Путь до файла с картой скомпилированных asset`ов
     * @var string
     */
    protected $mapPath;
    
    /**
     * Список скомпилированных ассетов
     * ['asset.name' => 'public-asset.file']
     * @var mixed|array
     */
    protected $assets;
    
    /**
     * Флаг говоряций о том, что файл карты был загружен
     * @var bool
     */
    protected $loaded;

    /**
     * @param string $mapPath - Путь до файла с картой скомпилированных asset`ов
     */
    public function __construct($mapPath)
    {
        $this->mapPath = $mapPath;
        $this->assets  = [];
    }

    /**
     * Добавить asset в карту
     * @param string $assetName - название asset`а
     * @param string $assetPath - путь до файла в котором сохранен asset
     */
    public function addAsset($assetName, $assetPath)
    {
        $this->assets[$assetName] = $assetPath;
    }

    /**
     * Сохранить карту
     */
    public function save()
    {
        $json = json_encode($this->assets);
        
        file_put_contents($this->mapPath, $json);
    }

    /**
     * Получить путь до файла asset`а по его имени
     * @param string $assetName
     * @return string
     * @throws \Exception
     */
    public function getAssetPath($assetName)
    {
        if (!$this->loaded) {
            $this->load();
        }
        
        if (isset($this->assets[$assetName])) {
            return $this->assets[$assetName];
        }
        
        throw new \Exception("Asset '{$assetName}' not found");
    }
    
    /**
     * Загрузить карту
     */
    protected function load()
    {
        $json = file_get_contents($this->mapPath);
        
        $this->assets = array_merge($this->assets, json_decode($json, true));
    }
}
