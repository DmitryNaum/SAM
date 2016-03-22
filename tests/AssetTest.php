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
        $mapPath          = 'vfs://asset/map.json';

        $manifest = [
            'assetBasePath' => 'vfs://asset/build',
            'resultMapPath' => $mapPath,
            'assets'        => []
        ];
        
        $map = [
            'some.js'  => 'some/asset.js',
            'some.css' => 'some/asset.css',
        ];

        file_put_contents($manifestFilePath, json_encode($manifest));
        file_put_contents($mapPath, json_encode($map));

        Asset::$manifestFilePath = $manifestFilePath;
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
    
    public function testUseRemoteJs()
    {
        Asset::useRemoteJs('http://some.host/some.js');
        
        $jsTags = Asset::renderJs();
        
        $this->assertContains('http://some.host/some.js', $jsTags);
    }

    public function testUseRemoteCss()
    {
        Asset::useRemoteCss('http://some.host/some.css');
        
        $cssTags = Asset::renderCss();

        $this->assertContains('http://some.host/some.css', $cssTags);
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

}
