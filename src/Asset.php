<?php

namespace Dmitrynaum\SAM;

use Dmitrynaum\SAM\Component\Manifest;

/**
 * Класс-фасад для удобной работы с AssetManager`ом
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class Asset
{

    /**
     * Путь до manifest файла
     * @var string
     */
    public static $manifestFilePath = 'sam.json';
   
    /**
     * Объект менеджера ассетов
     * @var AssetManager
     */
    protected static $assetManager;
    
    /**
     * Включить режим разработки
     */
    public function enableDevelopmentMode()
    {
        static::assetManager()->enableDevelopmentMode();
    }
    
    /**
     * Выключить режим разработки
     */
    public function disableDevelopmentMode()
    {
        static::assetManager()->disableDevelopmentMode();
    }
    
    /**
     * Включен ли режим разработки
     * @return bool
     */
    public function isDevelopmentModeEnabled()
    {
        return static::assetManager()->isDevelopmentModeEnabled();
    }

    /**
     * Использовать JavaScript Asset
     * @param string $assetName - имя ассета
     */
    public static function useJs($assetName)
    {
        static::assetManager()->useJs($assetName);
    }

    /**
     * Использовать CSS Asset
     * @param string $assetName - имя ассета
     */
    public static function useCss($assetName)
    {
        static::assetManager()->useCss($assetName);
    }

    /**
     * Получить html теги script с используемыми JavaScript asset`ами
     * @return string
     */
    public static function renderJs()
    {
        return static::assetManager()->renderJs();
    }

    /**
     * Получить html теги link с используемыми CSS asset`ами
     * @return string
     */
    public static function renderCss()
    {
        return static::assetManager()->renderCss();
    }
    
    /**
     * Получить AssetManager
     * @return AssetManager
     */
    protected function assetManager()
    {
        if (!static::$assetManager) {
            $manifest              = new Manifest(static::$manifestFilePath);
            static::$assetManager  = new AssetManager($manifest->resultMap());
        }
        
        return static::$assetManager;
    }
}
