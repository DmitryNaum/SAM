<?php

namespace Dmitrynaum\SAM;

use Dmitrynaum\SAM\Component\Manifest;

/**
 * Description of AssetBuilder
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetBuilder
{
    protected $needFreeze;
    protected $needCompress;
    protected $manifest;
    protected $manifestFilePath;

    public function __construct($manifestFilePath)
    {
        $this->manifestFilePath  = $manifestFilePath;
        $this->needCompress      = false;
        $this->needFreeze        = false;
        $this->resultMap         = [];
    }

    public function enableFreezing()
    {
        $this->needFreeze = true;
    }
    public function disableFreezing()
    {
        $this->needFreeze = false;
    }
    
    public function isFreezingEnabled()
    {
        return $this->needFreeze;
    }
    
    public function enableCompressor()
    {
        $this->needCompress = true;
    }
    
    public function disableCompressor()
    {
        $this->needCompress = false;
    }
    
    public function isCompressorEnabled()
    {
        return $this->needCompress;
    }
    
    public function build()
    {
        $this->clearAssetDir();
        $this->buildCss();
        $this->buildJs();
        $this->resultMap()->save();
    }
    
    protected function clearAssetDir()
    {
        $assetBasePath = $this->manifest()->getAssetBasePath();
        $files         = glob($assetBasePath.'/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    protected function buildCss()
    {
        $cssAssets = $this->manifest()->getCssAssets();
        
        $this->buildAssets($cssAssets);
    }
    
    protected function buildJs()
    {
        $jsAssets = $this->manifest()->getJsAssets();
        
        $this->buildAssets($jsAssets);
    }
    
    protected function buildAssets($assets)
    {
        foreach ($assets as $assetName => $assetFiles) {
            $assetPath    = $assetName;
            $assetContent = $this->readFiles($assetFiles);
                        
            if ($this->isCompressorEnabled()) {
                $compressor   = $this->getCompressor($assetName);
                $assetContent = $compressor->compress($assetContent);
            }
            
            if ($this->isFreezingEnabled()) {
                $assetHash = sha1($assetContent);
                $assetPath = $this->makeFreezingAssetFileName($assetName, $assetHash);
            }
            
            file_put_contents($assetPath, $assetContent);
            
            $this->resultMap()->addAsset($assetName, $assetPath);
        }
    }

    protected function makeFreezingAssetFileName($assetName, $assetHash = null)
    {
        if (!$assetHash) {
            return $assetName;
        }
        
        $fileInfo = pathinfo($assetName);
        $fileName = $fileInfo['filename'].'-'.$assetHash.'.'.$fileInfo['extension'];

        return $fileInfo['dirname'] . '/' .$fileName;
        
    }
    
    /**
     * 
     * @return Manifest
     */
    protected function manifest()
    {
        if (!$this->manifest) {
            $this->manifest = new Manifest($this->manifestFilePath);
        }
        
        return $this->manifest;
    }
    
    protected function resultMap()
    {
        return $this->manifest()->resultMap();
    }
    
    protected function readFiles($filePaths)
    {
        $filesContent = [];
        
        foreach ($filePaths as $filePath) {
            $filesContent[] = file_get_contents($filePath);
        }
        
        return join(PHP_EOL, $filesContent);
    }
    
    protected function getCompressor($assetPath)
    {
        $fileExtension =  pathinfo($assetPath, PATHINFO_EXTENSION);
        switch ($fileExtension['extension']) {
            case 'js':
                return new \MatthiasMullie\Minify\CSS();
            case 'css':
                return new \MatthiasMullie\Minify\JS();
        }
    }
}
