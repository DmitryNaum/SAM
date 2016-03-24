<?php

namespace Dmitrynaum\SAM;

/**
 * Менеджер для работы с asset`ами
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetManager
{
    /**
     * Список используемых JavaScript asset`ов
     * @var string[]
     */
    protected $js = [];
    
    /**
     * Список inline JS
     * @var array
     */
    protected $inlineJs = [];
    
    /**
     * Список используемых Css asset`ов
     * @var string[]
     */
    protected $css = [];
        
    /**
     * Список inline Css
     * @var array
     */
    protected $inlineCss = [];

    /**
     * Карта asset`ов
     * @var Component\AssetMap
     */
    protected $map;

    /**
     * Режим разработки
     * @var bool
     */
    protected $developmentMode;
    
    /**
     * Host на котором висит dev сервер
     * @var string
     */
    protected $developmentHost = 'http://127.0.0.1:8652/';

    /**
     * @param \Dmitrynaum\SAM\Component\AssetMap $map - Карта asset`ов
     */
    public function __construct(Component\AssetMap $map)
    {
        $this->map             = $map;
        $this->developmentMode = false;
    }
    
    /**
     * Включить режим разработки
     */
    public function enableDevelopmentMode()
    {
        $this->developmentMode = true;
    }
    
    /**
     * Выключить режим разработки
     */
    public function disableDevelopmentMode()
    {
        $this->developmentMode = false;
    }
    
    /**
     * Включен ли режим разработки
     * @return bool
     */
    public function isDevelopmentModeEnabled()
    {
        return $this->developmentMode;
    }

    /**
     * Использовать JavaScript по названию asset`а или url
     * @param string $assetNameOrUrl - имя ассета
     */
    public function useJs($assetNameOrUrl)
    {
        if (!in_array($assetNameOrUrl, $this->js)) {
            $this->js[] = $assetNameOrUrl;
        }
    }

    /**
     * Использовать CSS по названию asset`а или url
     * @param string $assetNameOrUrl - Имя ассета
     */
    public function useCss($assetNameOrUrl)
    {
        if (!in_array($assetNameOrUrl, $this->css)) {
            $this->css[] = $assetNameOrUrl;
        }
    }
    
    /**
     * Добавить inline css код
     * @param string $css
     */
    public function addInlineCss($css)
    {
        $this->inlineCss[] = $css;
    }
    
    /**
     * Добавить inline JavaScript код
     * @param string $js
     */
    public function addInlineJs($js)
    {
        $this->inlineJs[] = $js;
    }

    /**
     * Получить html теги script с используемыми JavaScript asset`ами
     * @param array $attributes Массив атрибутов тега
     * ['type' => 'text/javascript']
     * ['async', 'defer']
     * @return string
     */
    public function renderJs(array $attributes = [])
    {
        $jsUrls = $this->getUrls($this->js);
        
        $jsTags = '';
        foreach ($jsUrls as $jsUrl) {
            $jsTags .= $this->wrapJsLink($jsUrl, $attributes);
        }

        return $jsTags;
    }

    /**
     * Получить html теги link с используемыми CSS asset`ами
     * @return string
     */
    public function renderCss()
    {
        $cssUrls = $this->getUrls($this->css);
        
        $cssTags = '';
        foreach ($cssUrls as $cssUrl) {
            $cssTags .= $this->wrapCssLink($cssUrl);
        }

        return $cssTags;
    }
    
    /**
     * Получить теги script с inline js или пустую строку если их нет
     * @return string
     */
    public function renderInlineJs()
    {
        $jsCode = join(";\n", $this->inlineJs);
        
        return $jsCode ? "<script>{$jsCode}</script>" : '';
    }
    
    /**
     * Получить теги style с inline css или пустую строку если их нет
     * @return string
     */
    public function renderInlineCss()
    {
        $cssCode = join("\n", $this->inlineCss);
        
        return $cssCode ? "<style>{$cssCode}</style>" : '';
    }
    
    /**
     * Получить используемые css ассеты и url
     * @return array
     */
    public function getUsedCss()
    {
        return $this->css;
    }
    
    /**
     * Получить используемые js asset`ы и url
     * @return array
     */
    public function getUsedJs()
    {
        return $this->js;
    }
    
    /**
     * Удалить используемый css по названию asset`а или url
     * @param string $cssAssetNameOrUrl
     */
    public function removeCss($cssAssetNameOrUrl)
    {
        foreach ($this->css as $index => $assetName) {
            if ($assetName == $cssAssetNameOrUrl) {
                unset($this->css[$index]);
            }
        }
    }
    
    /**
     * Удалить используемый js по названию asset`а или url
     * @param string $jsAssetNameOrUrl
     */
    public function removeJs($jsAssetNameOrUrl)
    {
        foreach ($this->js as $index => $assetName) {
            if ($assetName == $jsAssetNameOrUrl) {
                unset($this->js[$index]);
            }
        }
    }
    
    /**
     * Удалить все используемые css asset`ы
     */
    public function removeAllCss()
    {
        $this->css = [];
    }
    
    /**
     * Удалить все используемые js asset`ы
     */
    public function removeAllJs()
    {
        $this->js = [];
    }

    /**
     * Получить ссылку на asset по его имени
     * @param string $assetName название asset`а
     * @return string
     */
    protected function getAssetUrl($assetName)
    {
        if ($this->isDevelopmentModeEnabled()) {
            $assetUrl = "{$this->developmentHost}?asset=$assetName";
        } else {
            $assetUrl = $this->map->getAssetPath($assetName);
        }
        
        return $assetUrl;
    }
    
    /**
     * Обернуть ссылку на JS файл в тег script
     * @param string $pathToJs
     * @param array $attributes Массив атрибутов тега
     * ['Имя атрибута' => 'значение']
     * ['атрибут']
     * @return string
     */
    protected function wrapJsLink($pathToJs, array $attributes = [])
    {
        
        $attrs = [];
        foreach ($attributes as $arrtibute => $value) {
            if (is_numeric($arrtibute)) {
                $attrs[] = $value;
            } else {
                $attrs[] = "{$arrtibute}='{$value}'";
            }
        }
        
        $attributesString = join(' ', $attrs);
        return "<script src='{$pathToJs}' {$attributesString}></script>";
    }
    
    /**
     * Обернуть ссылку на Css файл в тег script
     * @param string $pathToCss
     * @return string
     */
    protected function wrapCssLink($pathToCss)
    {
        return "<link rel='stylesheet' type='text/css' href='{$pathToCss}' />";
    }
    
    /**
     * Проверяет является ли ссылка URL
     * @param string $string
     * @return bool
     */
    protected function isUrl($string)
    {
        if (strpos($string, '//') === 0) {
            $string = "http:{$string}";
        }
        
        return filter_var($string, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Получить ссылки
     * @param array $array
     */
    protected function getUrls($array)
    {
        $urls = [];
        foreach ($array as $assetNameOrUrl) {
            if ($this->isUrl($assetNameOrUrl)) {
                $urls[] = $assetNameOrUrl;
            } else {
                $urls[] = $this->getAssetUrl($assetNameOrUrl);
            }
        }
        
        return $urls;
    }
}
