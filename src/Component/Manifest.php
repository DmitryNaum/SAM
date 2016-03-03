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
     * @var string
     */
    protected $jsAssets;

    /**
     * Список css ассетов
     * @var string
     */
    protected $cssAssets;
    protected $resultMap;

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
        $content = file_get_contents($this->filePath);
        $json    = json_decode($content);

        $this->assetBasePath = $json->assetBasePath;
        $this->resultMapPath = $json->resultMapPath;

        $this->assetFiles = [];
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

    public function getJsAssets()
    {
        return $this->jsAssets;
    }

    public function getCssAssets()
    {
        return $this->cssAssets;
    }

    /**
     * Получить объект карты результатов билдинга
     * @return \Dmitrynaum\SAM\Component\ResultMap
     */
    public function resultMap()
    {
        if (!$this->resultMap) {
            $this->resultMap = new ResultMap($this->assetBasePath, $this->resultMapPath);
        }
        return $this->resultMap;
    }

    public function getAssetBasePath()
    {
        return $this->assetBasePath;
    }

}
