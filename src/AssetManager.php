<?php

namespace Dmitrynaum\SAM;

/**
 * Description of AssetManager
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetManager
{
    protected $js = [];
    
    protected $css = [];
    
    /**
     *
     * @var Component\ResultMap
     */
    protected $map;


    public function __construct(Component\ResultMap $map)
    {
        $this->map = $map;
    }
    
    public function useJs($assetName)
    {
        $this->js[] = $assetName;
    }
    
    public function useCss($assetName)
    {
        $this->css[] = $assetName;
    }
    
    public function renderJs()
    {
        $jsTags = [];
        
        foreach ($this->js as $assetName) {
            $pathToAssetFile = $this->map->getPath($assetName);
            $jsTags[] = "<script src='{$pathToAssetFile}'></script>";
        }
        
        return $jsTags;
    }
    
    public function renderCss()
    {
        $cssTags = [];
        
        foreach ($this->css as $assetName) {
            $pathToAssetFile = $this->map->getPath($assetName);
            $cssTags[] = "<link rel='stylesheet' type='text/css' href='{$pathToAssetFile}' />";
        }
        
        return $cssTags;
    }
    
}
