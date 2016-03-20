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


        $manifestFilePath = 'vfs://asset/manifest.json';
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

        $this->assertContains("<script src='vfs://asset/build/some/asset.js'></script>", $js);
    }

    public function testGetCss()
    {
        Asset::useCss('some.css');

        $css = Asset::renderCss();
        
        $this->assertContains("<link rel='stylesheet' type='text/css' href='vfs://asset/build/some/asset.css' />", $css);
    }

}
