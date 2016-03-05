<?php

namespace tests;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Description of AssetBuilderTest
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        parent::tearDown();
    }

    protected function getManifestPath()
    {
        vfsStream::setup();

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('asset'));


        $manifestFilePath = 'vfs://asset/manifest.json';

        $manifest = [
            'assetBasePath' => 'vfs://asset/build',
            'resultMapPath' => 'vfs://asset/map.json',
            'assets'        => [
                "vfs://asset/app.css" => [
                    "vfs://asset/first.css",
                    "vfs://asset/second.css"
                ],
                "vfs://asset/app.js"  => [
                    "vfs://asset/first.js",
                    "vfs://asset/second.js"
                ]
            ]
        ];

        file_put_contents($manifestFilePath, json_encode($manifest));

        file_put_contents('vfs://asset/first.js', 'var a=3;');
        file_put_contents('vfs://asset/second.js', 'var b=4;');
        file_put_contents('vfs://asset/first.css', '.one{border:none;}');
        file_put_contents('vfs://asset/second.css', '.two{border:none;}');

        return $manifestFilePath;
    }

    public function testBuild_fileCreated()
    {
        $manifestFilePath = $this->getManifestPath();
        $builder          = new \Dmitrynaum\SAM\AssetBuilder($manifestFilePath);

        $builder->build();
        
        $this->assertTrue(file_exists('vfs://asset/app.css'));
        $this->assertTrue(file_exists('vfs://asset/app.js'));
    }
    

    public function testBuild_mapSaved()
    {
        $manifestFilePath = $this->getManifestPath();
        $builder          = new \Dmitrynaum\SAM\AssetBuilder($manifestFilePath);

        $builder->build();
        
        $this->assertTrue(file_exists('vfs://asset/map.json'));
    }
    
    public function testBuild_hasContent()
    {
        $manifestFilePath = $this->getManifestPath();
        $builder          = new \Dmitrynaum\SAM\AssetBuilder($manifestFilePath);

        $builder->build();
        
        $cssContent = file_get_contents('vfs://asset/app.css');
        
        $this->assertContains('.one{border:none;}', $cssContent);
        $this->assertContains('.two{border:none;}', $cssContent);
        
        $jsContent = file_get_contents('vfs://asset/app.js');
                
        $this->assertContains('var a=3;', $jsContent);
        $this->assertContains('var b=4;', $jsContent);
    }

}
