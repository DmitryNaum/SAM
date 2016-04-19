<?php

namespace Dmitrynaum\SAM\Test;

use Dmitrynaum\SAM\AssetManager;
use Dmitrynaum\SAM\Component\AssetMap;
use Dmitrynaum\SAM\Component\Manifest;
use org\bovigo\vfs\vfsStream;
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
        
        $manifestFilePath = 'vfs://asset/sam.json';

        $manifestData = [
            "devServerAddress" => "127.0.0.1:8652",
            'assetBasePath'    => 'vfs://asset/build',
            'rootDir'          => '',
            'resultMapPath'    => 'map.json',
            'assets'           => []
        ];
        
        $assets = [
            'some/asset.js'  => '/assets/some/asset.js',
            'some/asset.css' => '/assets/some/asset.css',
        ];

        file_put_contents('vfs://asset/map.json', json_encode($assets));
        file_put_contents($manifestFilePath, json_encode($manifestData));
        
        $manifest = new Manifest($manifestFilePath);

        return new AssetManager($manifest);
    }

    public function testRenderJs()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useJs('some/asset.js');

        $jsTags = $assetManager->renderJs();

        $this->assertRegExp("/\<script.+src\=\'\/assets\/some\/asset.js\'.+\>\<\/script\>/", $jsTags);
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
    
    public function testUseJs_byUrlWithSchema()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->useJs('http://some.host/some.js');
        
        $jsTags = $assetManager->renderJs();
        $this->assertContains('http://some.host/some.js', $jsTags);
    }
    
    public function testUseJs_byUrlWithoutSchema()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->useJs('//some.host/some.js');
        
        $jsTags = $assetManager->renderJs();
        $this->assertContains('//some.host/some.js', $jsTags);
    }
    
    public function testUseCss_byUrlWithSchema()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->useCss('http://some.host/some.css');
        
        $cssTags = $assetManager->renderCss();
        $this->assertContains('http://some.host/some.css', $cssTags);
    }
    
    public function testUseCss_byUrlWithoutSchema()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->useCss('//some.host/some.css');
        
        $cssTags = $assetManager->renderCss();
        $this->assertContains('//some.host/some.css', $cssTags);
    }
    
    public function testIsDevelopmentModeEnabled()
    {
        $assetManager = $this->getAssetManager();
        
        $this->assertFalse($assetManager->isDevelopmentModeEnabled());
    }
    
    public function testEnableDevelopmentMode()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->enableDevelopmentMode();
        
        $this->assertTrue($assetManager->isDevelopmentModeEnabled());
    }
    
    public function testDisableDevelopmentMode()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->enableDevelopmentMode();
        
        $assetManager->disableDevelopmentMode();
        
        $this->assertFalse($assetManager->isDevelopmentModeEnabled());
    }

    public function testRenderJs_enabledDevelopmentMode()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->enableDevelopmentMode();
        $assetManager->useJs('some/asset.js');
        
        $jsTags = $assetManager->renderJs();
        
        $this->assertContains('//127.0.0.1:8652/?asset=some/asset.js', $jsTags);
    }

    public function testRenderCss_enabledDevelopmentMode()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->enableDevelopmentMode();
        $assetManager->useCss('some/asset.css');
        
        $cssTags = $assetManager->renderCss();
        
        $this->assertContains('//127.0.0.1:8652/?asset=some/asset.css', $cssTags);
    }
    
    public function testRenderJs_withDeferAttribute()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useJs('some/asset.js');

        $jsTags = $assetManager->renderJs(['defer']);

        $this->assertRegExp('/defer/', $jsTags);
    }
    
    public function testRenderJs_withTypeAttribute()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useJs('some/asset.js');

        $jsTags = $assetManager->renderJs(['type' => 'text/javascript']);

        $this->assertRegExp("/type\=\'text\/javascript\'/", $jsTags);
    }
    
     
    public function testRenderInlineJs()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->addInlineJs("alert('test')");
        $assetManager->addInlineJs("alert('test2')");

        $inlineJs = $assetManager->renderInlineJs();
        
        $this->assertEquals("<script>alert('test');\nalert('test2')</script>", $inlineJs);
    }
    
    public function testRenderInlineCss()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->addInlineCss(".my-class1{ width:100% }");
        $assetManager->addInlineCss(".my-class2{ height:100% }");

        $inlineCss = $assetManager->renderInlineCss();
        
        $this->assertEquals("<style>.my-class1{ width:100% }\n.my-class2{ height:100% }</style>", $inlineCss);
    }
    
    public function testRenderInlineJs_NoJs()
    {
        $assetManager = $this->getAssetManager();

        $inlineJs = $assetManager->renderInlineJs();
        
        $this->assertEmpty($inlineJs);
    }
    
    public function testRenderInlineCss_noCss()
    {
        $assetManager = $this->getAssetManager();

        $inlineCss = $assetManager->renderInlineCss();
        
        $this->assertEmpty($inlineCss);
    }
    
    public function testGetUsedCss()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useCss('some/asset.css');
        
        $usedAssets = $assetManager->getUsedCss();
        
        $this->assertEquals(['some/asset.css'], $usedAssets);
    }
    
    public function testGetUsedJs()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useJs('some/asset.js');
        
        $usedAssets = $assetManager->getUsedJs();
        
        $this->assertEquals(['some/asset.js'], $usedAssets);
    }
    
    public function testRemoveCss()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useCss('some/asset.css');
        
        $assetManager->removeCss('some/asset.css');
        
        $usedAssets = $assetManager->getUsedCss();
        
        $this->assertEmpty($usedAssets);
    }
    
    public function testRemoveJs()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useJs('some/asset.js');
        
        $assetManager->removeJs('some/asset.js');
        
        $usedAssets = $assetManager->getUsedJs();
        
        $this->assertEmpty($usedAssets);
    }
    
    public function testRemoveAllJs()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useJs('some/asset.js');
        
        $assetManager->removeAllJs();
        
        $usedAssets = $assetManager->getUsedJs();
        
        $this->assertEmpty($usedAssets);
    }
    
    
    public function testRemoveAllCss()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useCss('some/asset.css');
        
        $assetManager->removeAllCss();
        
        $usedAssets = $assetManager->getUsedCss();
        
        $this->assertEmpty($usedAssets);
    }
    
    public function testUseCss_assetUsedTwice_returnOne()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useCss('some/asset.css');
        $assetManager->useCss('some/asset.css');
        
        $usedAssets = $assetManager->getUsedCss();
        
        $this->assertEquals(['some/asset.css'], $usedAssets);
    }
    
    
    public function testUseJs_assetUsedTwice_returnOne()
    {
        $assetManager = $this->getAssetManager();
        $assetManager->useJs('some/asset.js');
        $assetManager->useJs('some/asset.js');
        
        $usedAssets = $assetManager->getUsedJs();
        
        $this->assertEquals(['some/asset.js'], $usedAssets);
    }
}
