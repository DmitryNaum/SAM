<?php

namespace Dmitrynaum\SAM\Component;

/**
 * Description of ResultMap
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class ResultMap
{

    protected $assetbasePath;
    protected $mapPath;
    protected $assets;
    protected $loaded;

    public function __construct($assetbasePath, $mapPath)
    {
        $this->assetbasePath = $assetbasePath;
        $this->mapPath       = $mapPath;
        $this->assets        = [];
    }

    public function addAsset($assetName, $assetPath)
    {
        $this->assets[$assetName] = $assetPath;
    }

    public function save()
    {
        $json = json_encode($this->assets);
        
        file_put_contents($this->mapPath, $json);
    }

    public function getPath($assetName)
    {
        if (!$this->loaded) {
            $this->load();
        }
        
        if (isset($this->assets[$assetName])) {
            return $this->assetbasePath.'/'.$this->assets[$assetName];
        }
        
        throw new \Exception("Asset '{$assetName}' not found");
    }
    
    protected function load()
    {
        $json = file_get_contents($this->mapPath);
        
        $this->assets = json_decode($json, true);
    }
}
