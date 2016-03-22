<?php

namespace Dmitrynaum\SAM\Test;

use org\bovigo\vfs\vfsStream;
use Dmitrynaum\SAM\AssetManager;
use org\bovigo\vfs\vfsStreamWrapper;
use Dmitrynaum\SAM\Component\AssetMap;
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
    
    public function testUseRemoteJs()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->useRemoteJs('http://some.host/some.js');
        
        $jsTags = $assetManager->renderJs();
        $this->assertContains('http://some.host/some.js', $jsTags);
    }
    
    public function testUseRemoteCss()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->useRemoteCss('http://some.host/some.css');
        
        $cssTags = $assetManager->renderCss();
        $this->assertContains('http://some.host/some.css', $cssTags);
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
        
        $this->assertContains('http://127.0.0.1:8652/?asset=some/asset.js', $jsTags);
    }

    public function testRenderCss_enabledDevelopmentMode()
    {
        $assetManager = $this->getAssetManager();
        
        $assetManager->enableDevelopmentMode();
        $assetManager->useCss('some/asset.css');
        
        $cssTags = $assetManager->renderCss();
        
        $this->assertContains('http://127.0.0.1:8652/?asset=some/asset.css', $cssTags);
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
}
