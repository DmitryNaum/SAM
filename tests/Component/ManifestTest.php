<?php
namespace Component;

use Dmitrynaum\SAM\Component\Manifest;
use\Dmitrynaum\SAM\Component\AssetMap;

/**
 * Description of ManifestTest
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class ManifestTest extends \PHPUnit_Framework_TestCase
{
    protected function makeManifest()
    {
        return new Manifest(__DIR__.'/Fake/manifest.json');
    }
    
    public function testGetJsAssets()
    {
        $manifest = $this->makeManifest();
        
        $assetFiles = $manifest->getJsAssets();
        
        $expected = [
            "app.js" => [
                "asset/js/1st.js",
                "asset/js/2nd.js",
            ]
        ];
        
        $this->assertEquals($expected, $assetFiles);
    }
    
    public function testGetCssAssets()
    {
        $manifest = $this->makeManifest();
        
        $assetFiles = $manifest->getCssAssets();
        
        $expected = [
            "app.css" => [
                "asset/css/1st.css",
                "asset/css/2nd.css",
            ]
        ];
        
        $this->assertEquals($expected, $assetFiles);
    }
    
    public function testResultMap()
    {
        $manifest = $this->makeManifest();
        
        $resultMap = $manifest->resultMap();
        
        $this->assertInstanceOf(AssetMap::class, $resultMap);
    }
}
