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

}
