<?php

namespace Dmitrynaum\SAM;

/**
 * Менеджер для работы с asset`ами
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetManager
{
    /**
     * Список используемых JavaScript asset`ов
     * @var string
     */
    protected $js = [];

    /**
     * Список используемых Css asset`ов
     * @var string
     */
    protected $css = [];

    /**
     * Карта asset`ов
     * @var Component\AssetMap
     */
    protected $map;

    /**
     * Режим разработки
     * @var bool
     */
    protected $developmentMode;
    
    /**
     * Host на котором висит сервер
     * @var string
     */
    protected $developmentHost = 'http://127.0.0.1:8652';

    /**
     * @param \Dmitrynaum\SAM\Component\AssetMap $map - Карта asset`ов
     */
    public function __construct(Component\AssetMap $map)
    {
        $this->map             = $map;
        $this->developmentMode = false;
    }
    
    /**
     * Включить режим разработки
     */
    public function enableDevelopmentMode()
    {
        $this->developmentMode = true;
    }
    
    /**
     * Выключить режим разработки
     */
    public function disableDevelopmentMode()
    {
        $this->developmentMode = false;
    }
    
    /**
     * Включен ли режим разработки
     * @return bool
     */
    public function isDevelopmentModeEnabled()
    {
        return $this->developmentMode;
    }

    /**
     * Использовать JavaScript Asset
     * @param string $assetName - имя ассета
     */
    public function useJs($assetName)
    {
        $this->js[] = $assetName;
    }

    /**
     * Использовать CSS Asset
     * @param string $assetName - имя ассета
     */
    public function useCss($assetName)
    {
        $this->css[] = $assetName;
    }

    /**
     * Получить html теги script с используемыми JavaScript asset`ами
     * @return string
     */
    public function renderJs()
    {
        $jsTags = [];

        foreach ($this->js as $assetName) {
            if ($this->isDevelopmentModeEnabled()) {
                $pathToAssetFile = "{$this->developmentHost}?asset=$assetName";
            } else {
                $pathToAssetFile = $this->map->getAssetPath($assetName);
            }
            $jsTags[] = "<script src='{$pathToAssetFile}'></script>";
        }

        return join('', $jsTags);
    }

    /**
     * Получить html теги link с используемыми CSS asset`ами
     * @return string
     */
    public function renderCss()
    {
        $cssTags = [];

        foreach ($this->css as $assetName) {
            if ($this->isDevelopmentModeEnabled()) {
                $pathToAssetFile = "{$this->developmentHost}?asset=$assetName";
            } else {
                $pathToAssetFile = $this->map->getAssetPath($assetName);
            }
            $cssTags[] = "<link rel='stylesheet' type='text/css' href='{$pathToAssetFile}' />";
        }

        return join('', $cssTags);
    }
}
