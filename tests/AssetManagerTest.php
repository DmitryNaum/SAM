<?php

namespace Dmitrynaum\SAM\Test;

use org\bovigo\vfs\vfsStream;
use Dmitrynaum\SAM\AssetManager;
use Dmitrynaum\SAM\Component\AssetMap;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Description of TestAssetManager
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetManagerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        vfsStream::setup();
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('asset'));
    }

    protected function getAssetManager()
    {
        $assets = [
            'some/asset.js'  => '/assets/some/asset.js',
            'some/asset.css' => '/assets/some/asset.css',
        ];

        file_put_contents('vfs://asset/map.json', json_encode($assets));

        $map = new AssetMap('vfs://asset/map.json');

        return new AssetManager($map);
    }

    public function testRenderJs()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useJs('some/asset.js');

        $jsTags = $assetManager->renderJs();

        $expectedTag = "<script src='/assets/some/asset.js'></script>";
        $this->assertContains($expectedTag, $jsTags);
    }

    public function testRenderCss()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useCss('some/asset.css');

        $cssTags = $assetManager->renderCss();

        $expectedTag = "<link rel='stylesheet' type='text/css' href='/assets/some/asset.css' />";
        $this->assertContains($expectedTag, $cssTags);
    }
    
    public function testRenderJs_assetNotFound()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useJs('/assets/some/not-exist-asset.js');

        $this->setExpectedException(\Exception::class);
        
        $jsTags = $assetManager->renderJs();

    }

}
