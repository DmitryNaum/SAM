<?php

namespace Dmitrynaum\SAM\Test\Component;

use Dmitrynaum\SAM\Component\AssetMap;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Description of AssetMapTest
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetMapTest extends \PHPUnit_Framework_TestCase
{
    protected function getAssetMap()
    {
        vfsStream::setup();
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('asset'));
        
        $mapPath = 'vfs://asset/map.json';
        $asset   = new AssetMap($mapPath);
        
        $assets = [
            'asset.js'  => 'asset-path.js',
        ];

        file_put_contents($mapPath, json_encode($assets));
        
        $map = new AssetMap($mapPath);
        
        return $map;
    }
    
    public function testAddAsset()
    {
        $map = $this->getAssetMap();
        
        $map->addAsset('asset.name', 'asset.path');
        
        $this->assertEquals('asset.path', $map->getAssetPath('asset.name'));
    }
    
    public function testGetAssetPath()
    {
        $map = $this->getAssetMap();
        
        $this->assertEquals('asset-path.js', $map->getAssetPath('asset.js'));
    }
    
    public function testSave()
    {
        $map = $this->getAssetMap();
        $map->addAsset('new.asset', 'new-asset.path');
        
        $map->save();
        
        $mapData = file_get_contents('vfs://asset/map.json');
        $this->assertRegExp('/new\.asset.+new-asset\.path/', $mapData);
        
    }
}
