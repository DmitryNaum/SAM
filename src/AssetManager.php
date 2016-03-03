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

    public function useJs($assetName)
    {
        $this->js[] = $assetName;
    }
    
    public function useCss($assetName)
    {
        $this->css[] = $assetName;
    }
    
    public function getUsedCss()
    {
        return $this->css;
    }
    
    public function getUsedJs()
    {
        return $this->js;
    }
    
    public function renderJs()
    {
        $jsTags = [];
        
        foreach ($this->getUsedJs() as $pathToJsAssetFile) {
            $jsTags[] = "<script src='{$pathToJsAssetFile}'></script>";
        }
        
        return $jsTags;
    }
    
    public function renderCss()
    {
        $cssTags = [];
        
        foreach ($this->getUsedCss() as $pathToJsAssetFile) {
            $cssTags[] = "<link href='{$pathToJsAssetFile}' rel='stylesheet'>";
        }
        
        return $cssTags;
    }
    
}
