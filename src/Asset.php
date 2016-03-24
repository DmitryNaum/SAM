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
     * Использовать JavaScript Asset или js по его url
     * @param string $assetNameOrUrl - имя asset`а или url
     */
    public static function useJs($assetNameOrUrl)
    {
        static::assetManager()->useJs($assetNameOrUrl);
    }

    /**
     * Использовать CSS Asset или CSS по его url
     * @param string $assetNameOrUrl - имя asset`а или url
     */
    public static function useCss($assetNameOrUrl)
    {
        static::assetManager()->useCss($assetNameOrUrl);
    }
    
    /**
     * Добавить inline Css код
     * @param string $css
     */
    public static function addInlineCss($css)
    {
        static::assetManager()->addInlineCss($css);
    }
    
    /**
     * Добавить inline JavaScript код
     * @param string $js
     */
    public static function addInlineJs($js)
    {
        static::assetManager()->addInlineJs($js);
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
     * Получить теги style с inline css или пустую строку если их нет
     * @return string
     */
    public static function renderInlineCss()
    {
        return static::assetManager()->renderInlineCss();
    }
    
    /**
     * Получить теги script с inline js или пустую строку если их нет
     * @return string
     */
    public static function renderInlineJs()
    {
        return static::assetManager()->renderInlineJs();
    }
    
    /**
     * Получить используемые css ассеты
     * @return array
     */
    public static function getUsedCss()
    {
        return static::assetManager()->getUsedCss();
    }
    
    /**
     * Получить используемые js asset`ы
     * @return array
     */
    public static function getUsedJs()
    {
        return static::assetManager()->getUsedJs();
    }

    /**
     * Удалить используемый css asset по его названию или url
     * @param string $cssAssetNameOrUrl - название asset`а или url
     */
    public static function removeCss($cssAssetNameOrUrl)
    {
        static::assetManager()->removeCss($cssAssetNameOrUrl);
    }
    
    /**
     * Удалить используемый js asset по его названию или url
     * @param string $jsAssetNameOrUrl название asset`а или url
     */
    public static function removeJs($jsAssetNameOrUrl)
    {
        static::assetManager()->removeJs($jsAssetNameOrUrl);
    }
    
    /**
     * Удалить все используемые js asset`ы
     */
    public static function removeAlljs()
    {
        static::assetManager()->removeAllJs();
    }
    
    /**
     * Удалить все используемые css asset`ы
     */
    public static function removeAllCss()
    {
        static::assetManager()->removeAllCss();
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
