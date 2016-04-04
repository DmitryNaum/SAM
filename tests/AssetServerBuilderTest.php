<?php

namespace Dmitrynaum\SAM\Test;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use Dmitrynaum\SAM\AssetBuilder;
use \Dmitrynaum\SAM\AssetServerBuilder;

/**
 * Description of AssetServerBuilder
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetServerBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $assetBasePath = 'vfs://asset/build';

    protected function getManifestPath()
    {
        vfsStream::setup();

        $rootDir  = new vfsStreamDirectory('asset');
        $buildDir = new vfsStreamDirectory('build');
        $someDir  = new vfsStreamDirectory('somedir');

        $rootDir->addChild($buildDir);
        $buildDir->addChild($someDir);

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot($rootDir);

        $manifestFilePath = 'vfs://asset/sam.json';

        $manifest = [
            'devServerAddress' => "127.0.0.1:8080",
            'assetBasePath' => $this->assetBasePath,
            'resultMapPath' => 'vfs://asset/map.json',
            'assets'        => [
                "app.css" => [
                    "vfs://asset/first.css",
                    "vfs://asset/second.css"
                ],
                "app.js"  => [
                    "vfs://asset/first.js",
                    "vfs://asset/second.js"
                ],
                "jquery.js" => [
                    'vfs://asset/jquery.js'
                ],
                "app-jq.js" => [
                    "app.js",
                    "jquery.js"
                ],
                "deep-extending.js" => [
                    'app-jq.js'
                ],
            ]
        ];

        file_put_contents($manifestFilePath, json_encode($manifest));

        file_put_contents('vfs://asset/first.js', 'var a=3;');
        file_put_contents('vfs://asset/second.js', 'var b=4;');
        file_put_contents('vfs://asset/jquery.js', '$(){}');
        file_put_contents('vfs://asset/first.css', '.one{border:none;}');
        file_put_contents('vfs://asset/second.css', '.two{border:none;}');

        return $manifestFilePath;
    }

    /**
     * 
     * @return AssetBuilder
     */
    protected function makeBuilder()
    {
        $manifestFilePath = $this->getManifestPath();
        $builder          = new AssetServerBuilder($manifestFilePath, '');

        return $builder;
    }
    
    public function testGetAssetContentByName()
    {
        $builder = $this->makeBuilder();
        
        $assetContent = $builder->getAssetContentByName('jquery.js');
        
        $this->assertContains('$(){}', $assetContent);
    }
    
    public function testGetAssetContentByName_extendedAsset()
    {
        $builder = $this->makeBuilder();
        
        $assetContent = $builder->getAssetContentByName('app-jq.js');
        
        $this->assertContains('var a=3;', $assetContent);
        $this->assertContains('var b=4;', $assetContent);
        $this->assertContains('$(){}', $assetContent);
    }
    
    public function testGetAssetContentByName_assetNotFound()
    {
        $builder = $this->makeBuilder();
        
        $this->setExpectedException(\Exception::class, 'Asset not found', 404);
        
        $builder->getAssetContentByName('random.js');
    }
}
