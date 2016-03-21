<?php
namespace Dmitrynaum\SAM\Test\Component;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use Dmitrynaum\SAM\Component\AssetMap;

/**
 * Description of ManifestTest
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class ManifestTest extends \PHPUnit_Framework_TestCase
{
    protected function makeManifest($assets)
    {
        vfsStream::setup();

        $rootDir  = new vfsStreamDirectory('asset');
        $buildDir = new vfsStreamDirectory('build');
        $someDir  = new vfsStreamDirectory('somedir');

        $rootDir->addChild($buildDir);
        $buildDir->addChild($someDir);

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot($rootDir);

        $manifest = [
            'assetBasePath' => 'vfs://asset/build',
            'resultMapPath' => 'vfs://asset/map.json',
            'assets'        => $assets,
        ];

        file_put_contents('vfs://asset/sam.json', json_encode($manifest));

        file_put_contents('vfs://asset/first.js', 'var a=3;');
        
        $manifest = new \Dmitrynaum\SAM\Component\Manifest('vfs://asset/sam.json');
        
        return $manifest;
    }
    
    public function testGetJsAssets()
    {
        $expected = [
            "app.js" => [
                "vfs://asset/first.js",
                "vfs://asset/second.js"
            ]
        ];
        $manifest = $this->makeManifest($expected);
        
        $assetFiles = $manifest->getJsAssets();
        
        $this->assertEquals($expected, $assetFiles);
    }
    
    public function testGetCssAssets()
    {
        $expected = [
            "app.css" => [
                "vfs://asset/first.css",
                "vfs://asset/second.css"
            ]
        ];
        $manifest = $this->makeManifest($expected);
        
        $assetFiles = $manifest->getCssAssets();
        
        $this->assertEquals($expected, $assetFiles);
    }
    
    public function testResultMap()
    {
        $manifest = $this->makeManifest([]);
        
        $resultMap = $manifest->resultMap();
        
        $this->assertInstanceOf(AssetMap::class, $resultMap);
    }
}
