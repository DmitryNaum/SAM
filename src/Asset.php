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
    public static function enableDevelopmentMode()
    {
        static::assetManager()->enableDevelopmentMode();
    }
    
    /**
     * Выключить режим разработки
     */
    public static function disableDevelopmentMode()
    {
        static::assetManager()->disableDevelopmentMode();
    }
    
    /**
     * Включен ли режим разработки
     * @return bool
     */
    public static function isDevelopmentModeEnabled()
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
     * Использовать удаленный js.
     * Ссылка просто оборачивается в тег script
     * @param string $jsUrl Ссылка на js файл
     */
    public static function useRemoteJs($jsUrl)
    {
        static::assetManager()->useRemoteJs($jsUrl);
    }

    /**
     * Использовать удаленный css.
     * Ссылка просто оборачивается в тег link
     * @param string $cssUrl Ссылка на css файл
     */
    public static function useRemoteCss($cssUrl)
    {
        static::assetManager()->useRemoteCss($cssUrl);
    }

    /**
     * Получить html теги script используемых JavaScript asset`ов
     * @param array $attributes Массив атрибутов тега
     * ['Имя атрибута' => 'значение']
     * ['type' => 'text/javascript']
     * ['атрибут']
     * ['async', 'defer']
     * @return string
     */
    public static function renderJs(array $attributes = [])
    {
        return static::assetManager()->renderJs($attributes);
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
    protected static function assetManager()
    {
        if (!static::$assetManager) {
            $manifest              = new Manifest(static::$manifestFilePath);
            static::$assetManager  = new AssetManager($manifest->resultMap());
        }
        
        return static::$assetManager;
    }
}
