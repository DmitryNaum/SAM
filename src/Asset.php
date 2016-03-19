<?php

namespace Dmitrynaum\SAM;

use Dmitrynaum\SAM\Component\Manifest;

/**
 * Description of Asset
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class Asset
{

    public static $manifestFilePath = 'manifest.json';
    protected static $manifest;

    /**
     *
     * @var AssetManager
     */
    protected static $assetManager;

    public static function useJs($assetName)
    {
        static::assetManager()->useJs($assetName);
    }

    public static function useCss($assetName)
    {
        static::assetManager()->useCss($assetName);
    }

    public static function renderJs()
    {
        return static::assetManager()->renderJs();
    }

    public static function renderCss()
    {
        return static::assetManager()->renderCss();
    }
    
    protected function assetManager()
    {
        if (!static::$assetManager) {
            $manifest              = new Manifest(static::$manifestFilePath);
            static::$assetManager  = new AssetManager($manifest->resultMap());
        }
        
        return static::$assetManager;
    }
}
