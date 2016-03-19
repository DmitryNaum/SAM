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
     * @param \Dmitrynaum\SAM\Component\AssetMap $map - Карта asset`ов
     */
    public function __construct(Component\AssetMap $map)
    {
        $this->map = $map;
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
            $pathToAssetFile = $this->map->getAssetPath($assetName);
            $jsTags[]        = "<script src='{$pathToAssetFile}'></script>";
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
            $pathToAssetFile = $this->map->getAssetPath($assetName);
            $cssTags[]       = "<link rel='stylesheet' type='text/css' href='{$pathToAssetFile}' />";
        }
        
        return join('', $cssTags);
    }
}
