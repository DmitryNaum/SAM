<?php

namespace Dmitrynaum\SAM\Component;

/**
 * Класс для работы с манифестом
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class Manifest
{

    /**
     * Базовый путь до папки где будут храниться ассеты
     * @var string
     */
    protected $assetBasePath;

    /**
     * Путь до файла с картой построенных ассетов
     * @var string
     */
    protected $resultMapPath;

    /**
     * Путь до файла с манифестом
     * @var string
     */
    protected $filePath;

    /**
     * Список js ассетов
     * @var array
     */
    protected $jsAssets;

    /**
     * Список css ассетов
     * @var array
     */
    protected $cssAssets;
    
    /**
     * Карта скомпилированных asset`ов
     * @var AssetMap
     */
    protected $resultMap;

    /**
     * @param string $filePath - путь до файла sam.json
     */
    public function __construct($filePath)
    {
        $this->filePath  = $filePath;
        $this->jsAssets  = [];
        $this->cssAssets = [];

        $this->init();
    }

    /**
     * Считать настройки из файла и инициализироваться
     */
    protected function init()
    {
        // Считываем файл
        $content = file_get_contents($this->filePath);
        $json    = json_decode($content);

        // Заполняем свойства
        $this->assetBasePath = $json->assetBasePath;
        $this->resultMapPath = $json->resultMapPath;

        foreach ($json->assets as $assetName => $files) {
            $assetType = pathinfo($assetName, PATHINFO_EXTENSION);

            switch ($assetType) {
                case 'js':
                    $this->jsAssets[$assetName]  = $files;
                    break;
                case 'css':
                    $this->cssAssets[$assetName] = $files;
                    break;
            }
        }
    }

    /**
     * Получить все js asset`ы которые необходимо собрать
     * @return array
     */
    public function getJsAssets()
    {
        return $this->jsAssets;
    }

    /**
     * Получить все css asset`ы которые необходимо собрать
     * @return array
     */
    public function getCssAssets()
    {
        return $this->cssAssets;
    }

    /**
     * Получить объект карты результатов билдинга
     * @return AssetMap
     */
    public function resultMap()
    {
        if (!$this->resultMap) {
            $this->resultMap = new AssetMap($this->resultMapPath);
        }
        return $this->resultMap;
    }

    /**
     * Получить путь до папки где хранятся все asset`ы
     * @return string
     */
    public function getAssetBasePath()
    {
        return $this->assetBasePath;
    }
}
