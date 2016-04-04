<?php

namespace Dmitrynaum\SAM\Test;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use Dmitrynaum\SAM\AssetBuilder;

/**
 * Description of AssetBuilderTest
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetBuilderTest extends \PHPUnit_Framework_TestCase
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
                    "vfs://asset/jquery.js"
                ],
                "app-jq.js" => [
                    "app.js",
                    "jquery.js"
                ],
                "deep-extending.js" => [
                    "app-jq.js"
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
        $builder          = new AssetBuilder($manifestFilePath);

        return $builder;
    }

    public function testDisableFreezing()
    {
        $builder = $this->makeBuilder();

        $builder->enableFreezing();

        $this->assertTrue($builder->isFreezingEnabled());

        $builder->disableFreezing();

        $this->assertFalse($builder->isFreezingEnabled());
    }

    public function testDisableCompressor()
    {
        $builder = $this->makeBuilder();

        $builder->enableCompressor();

        $this->assertTrue($builder->isCompressorEnabled());

        $builder->disableCompressor();

        $this->assertFalse($builder->isCompressorEnabled());
    }

    public function testBuild_fileCreated()
    {
        $builder = $this->makeBuilder();

        $builder->build();

        $this->assertFileExists('vfs://asset/build/app.css');
        $this->assertFileExists('vfs://asset/build/app.js');
    }

    public function testBuild_withFreezing_fileCreated()
    {
        $builder = $this->makeBuilder();

        $builder->enableFreezing();
        $builder->build();

        $this->assertFileExists('vfs://asset/build/app-20453f0569619e019cfc05355a97451aba500ec2.css');
        $this->assertFileExists('vfs://asset/build/app-b89bd74e53b84c61dedc38a647771f3886624dfb.js');
    }
    
    public function testBuild_withCompressing_fileCreated()
    {
        $builder = $this->makeBuilder();

        $builder->enableCompressor();
        $builder->build();
        
        $cssContent = file_get_contents('vfs://asset/build/app.css');
        $jsContent = file_get_contents('vfs://asset/build/app.js');
                
        $this->assertEquals('.one{border:none}.two{border:none}', $cssContent);
        $this->assertEquals('var a=3;var b=4', $jsContent);
        
    }

    public function testBuild_assetDirectoryCleared()
    {
        $builder = $this->makeBuilder();

        file_put_contents("{$this->assetBasePath}/old-asset.js", '');
        file_put_contents("{$this->assetBasePath}/old-asset.css", '');

        $builder->build();

        $filesIntoBuildDir = [
            '.',
            '..',
            'app-jq.js',
            'app.css',
            'app.js',
            'deep-extending.js',
            'jquery.js',
        ];

        $this->assertEquals($filesIntoBuildDir, scandir($this->assetBasePath));
    }

    public function testBuild_mapSaved()
    {
        $builder = $this->makeBuilder();

        $builder->build();

        $this->assertTrue(file_exists('vfs://asset/map.json'));
    }

    public function testBuild_hasContent()
    {
        $builder = $this->makeBuilder();

        $builder->build();

        $cssContent = file_get_contents('vfs://asset/build/app.css');

        $this->assertContains('.one{border:none;}', $cssContent);
        $this->assertContains('.two{border:none;}', $cssContent);

        $jsContent = file_get_contents('vfs://asset/build/app.js');

        $this->assertContains('var a=3;', $jsContent);
        $this->assertContains('var b=4;', $jsContent);
    }
    
    
    public function testBuild_assetExtending()
    {
        $builder = $this->makeBuilder();
        
        $builder->build();
        
        $assetContent = file_get_contents('vfs://asset/build/app-jq.js');
        
        $this->assertContains('var a=3;', $assetContent);
        $this->assertContains('var b=4;', $assetContent);
        $this->assertContains('$(){}', $assetContent);
    }

}
