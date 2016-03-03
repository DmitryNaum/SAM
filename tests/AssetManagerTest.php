<?php

namespace tests;

use Dmitrynaum\SAM\AssetManager;

/**
 * Description of TestAssetManager
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetManagerTest extends \PHPUnit_Framework_TestCase
{
   protected function getAssetManager()
    {
        return new AssetManager();
    }

    public function testUseJs()
    {
        
        $assetManager = $this->getAssetManager();
        
        $assetManager->useJs('some/asset.js');
        
        $usedJs = $assetManager->getUsedJs();
        
        $this->assertContains('some/asset.js', $usedJs);
    }
    
    public function testUseCss()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->useCss('some/asset.css');
        
        $usedCss = $assetManager->getUsedCss();
        
        $this->assertContains('some/asset.css', $usedCss);
    }
    
    public function testRenderJsTags()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useJs('/assets/some/asset.js');
        
        $jsTags = $assetManager->renderJs();
        
        $expectedTag = "<script src='/assets/some/asset.js'></script>";
        $this->assertContains($expectedTag, $jsTags);
    }

    public function testRenderCssTags()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useCss('/assets/some/asset.css');
        
        $cssTags = $assetManager->renderCss();
        
        $expectedTag = "<link href='/assets/some/asset.css' rel='stylesheet'>";
        $this->assertContains($expectedTag, $cssTags);
    }
}
