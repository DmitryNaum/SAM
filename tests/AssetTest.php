<?php

namespace Dmitrynaum\SAM\Test;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use Dmitrynaum\SAM\Asset;

/**
 * Description of Asset
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        vfsStream::setup();

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('asset'));


        $manifestFilePath = 'vfs://asset/sam.json';

        $manifest = [
            'devServerAddress' => "127.0.0.1:8080",
            'assetBasePath'    => 'vfs://asset/build',
            'rootDir'          => '',
            'resultMapPath'    => 'map.json',
            'assets'           => []
        ];
        
        $map = [
            'some.js'  => 'some/asset.js',
            'some.css' => 'some/asset.css',
        ];

        file_put_contents($manifestFilePath, json_encode($manifest));
        file_put_contents('vfs://asset/map.json', json_encode($map));

        Asset::$manifestFilePath = $manifestFilePath;
    }
    
    protected function setUp()
    {
        parent::setUp();
        
        Asset::removeAllCss();
        Asset::removeAlljs();
    }

    public function testGetJs()
    {
        Asset::useJs('some.js');

        $js = Asset::renderJs();

        $this->assertRegExp("/\<script.+src\=\'some\/asset.js\'.+\>\<\/script\>/", $js);
    }

    public function testGetCss()
    {
        Asset::useCss('some.css');

        $css = Asset::renderCss();
        
        $this->assertContains("<link rel='stylesheet' type='text/css' href='some/asset.css' />", $css);
    }
    
    public function testUseJs_byUrlWithSchema()
    {
        Asset::useJs('http://some.host/some.js');
        
        $jsTags = Asset::renderJs();
        
        $this->assertContains('http://some.host/some.js', $jsTags);
    }
    
    public function testUseJs_byUrlWithoutSchema()
    {
        Asset::useJs('//some.host/some.js');
        
        $jsTags = Asset::renderJs();
        
        $this->assertContains('//some.host/some.js', $jsTags);
    }


    public function testUseCss_byUrlwithSchema()
    {
        Asset::useCss('http://some.host/some.css');
        
        $cssTags = Asset::renderCss();

        $this->assertContains('http://some.host/some.css', $cssTags);
    }
    
    public function testUseCss_byUrlwithoutSchema()
    {
        Asset::useCss('//some.host/some.css');
        
        $cssTags = Asset::renderCss();

        $this->assertContains('//some.host/some.css', $cssTags);
    }
    
    public function testIsDevelopmentModeEnabled()
    {
        $this->assertFalse(Asset::isDevelopmentModeEnabled());
    }
    
    public function testEnableDevelopmentMode()
    {
        Asset::enableDevelopmentMode();
        
        $this->assertTrue(Asset::isDevelopmentModeEnabled());
    }
    
    public function testDisableDevelopmentMode()
    {
        Asset::enableDevelopmentMode();

        Asset::disableDevelopmentMode();
        
        $this->assertFalse(Asset::isDevelopmentModeEnabled());
    }
    
    public function testRenderDeferJs_withDeferAttribute()
    {
        Asset::useJs('some.js');
        
        $jsTag = Asset::renderJs(['defer']);
        
        $this->assertRegExp('/defer/', $jsTag);
    }

    public function testRenderJs_withTypeAttribute()
    {
        Asset::useJs('some.js');
        
        $jsTag = Asset::renderJs(['type' => 'text/javascript']);
        
        $this->assertRegExp("/type\=\'text\/javascript\'/", $jsTag);
    }
    
    public function testRenderInlineCss()
    {
        Asset::addInlineCss('.my-class{ width:100% }');
        
        $inlineCss = Asset::renderInlineCss();
        
        $this->assertEquals('<style>.my-class{ width:100% }</style>', $inlineCss);
    }
    
    public function testRenderInlineJs()
    {
        Asset::addInlineJs('alert("test")');
        
        $inlineJs = Asset::renderInlineJs();
        
        $this->assertEquals('<script>alert("test")</script>', $inlineJs);
    }
    
    public function testGetUsedCss()
    {
        Asset::useCss('some.css');
        
        $usedAssets = Asset::getUsedCss();
        
        $this->assertEquals(['some.css'], $usedAssets);
    }
    
    public function testGetUsedJs()
    {
        Asset::useJs('some.js');
        
        $usedAssets = Asset::getUsedJs();
        
        $this->assertEquals(['some.js'], $usedAssets);
    }

    public function testRemoveCss()
    {
        Asset::useCss('some.css');
        
        Asset::removeCss('some.css');
        
        $usedAssets = Asset::getUsedCss();
        
        $this->assertEmpty($usedAssets);
    }
    
    public function testRemoveJs()
    {
        Asset::useJs('some.js');
        
        Asset::removeJs('some.js');
        
        $usedAssets = Asset::getUsedJs();
        
        $this->assertEmpty($usedAssets);
    }
    
    public function testRemoveAllJs()
    {
        Asset::useJs('some.js');
        
        Asset::removeAllJs();
        
        $usedAssets = Asset::getUsedJs();
        
        $this->assertEmpty($usedAssets);
    }

    
    public function testRemoveAllCss()
    {
        Asset::useCss('some.js');
        
        Asset::removeAllCss();
        
        $usedAssets = Asset::getUsedCss();
        
        $this->assertEmpty($usedAssets);
    }
}
