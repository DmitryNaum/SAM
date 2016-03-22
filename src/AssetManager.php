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
     * Список ссылок на удаленные JavaScript файлы
     * @var string[]
     */
    protected $remoteJs = [];

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
     * Список ссылок на удаленные css файлы
     * @var string[]
     */
    protected $remoteCss = [];
    
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
     * Использовать JavaScript Asset
     * @param string $assetName - имя ассета
     */
    public function useJs($assetName)
    {
        if (!in_array($assetName, $this->js)) {
            $this->js[] = $assetName;
        }
    }

    /**
     * Использовать CSS Asset
     * @param string $assetName - Имя ассета
     */
    public function useCss($assetName)
    {
        if (!in_array($assetName, $this->css)) {
            $this->css[] = $assetName;
        }
    }
    
    /**
     * Использовать удаленный css.
     * Ссылка просто оборачивается в тег link
     * @param string $cssUrl Ссылка на css файл
     */
    public function useRemoteCss($cssUrl)
    {
        $this->remoteCss[] = $cssUrl;
    }
    
    /**
     * Использовать удаленный js.
     * Ссылка просто оборачивается в тег script
     * @param string $jsUrl Ссылка на js файл
     */
    public function useRemoteJs($jsUrl)
    {
        $this->remoteJs[] = $jsUrl;
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
     * ['Имя атрибута' => 'значение']
     * ['type' => 'text/javascript']
     * ['атрибут']
     * ['async', 'defer']
     * @return string
     */
    public function renderJs(array $attributes = [])
    {
        $jsUrls = $this->remoteJs;
        
        foreach ($this->js as $assetName) {
            $jsUrls[] = $this->getAssetUrl($assetName);
        }
        
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
        $cssUrls = $this->remoteCss;
        
        foreach ($this->css as $assetName) {
            $cssUrls[] = $this->getAssetUrl($assetName);
        }
        
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
     * Получить используемые css ассеты
     * @return array
     */
    public function getUsedCss()
    {
        return $this->css;
    }
    
    /**
     * Получить используемые js asset`ы
     * @return array
     */
    public function getUsedJs()
    {
        return $this->js;
    }
    
    /**
     * Удалить используемый css asset по его имени
     * @param string $cssAssetName
     */
    public function removeCss($cssAssetName)
    {
        foreach ($this->css as $index => $assetName) {
            if ($assetName == $cssAssetName) {
                unset($this->css[$index]);
            }
        }
    }
    
    /**
     * Удалить используемый js asset по его имени
     * @param string $jsAssetName
     */
    public function removeJs($jsAssetName)
    {
        foreach ($this->js as $index => $assetName) {
            if ($assetName == $jsAssetName) {
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
     * @param string $pathToJsFile
     * @param array $attributes Массив атрибутов тега
     * ['Имя атрибута' => 'значение']
     * ['type' => 'text/javascript']
     * ['атрибут']
     * ['async', 'defer']
     * @return string
     */
    protected function wrapJsLink($pathToJsFile, array $attributes = [])
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
        return "<script src='{$pathToJsFile}' {$attributesString}></script>";
    }
    
    /**
     * Обернуть ссылку на Css файл в тег script
     * @param string $pathToCssFile
     * @return string
     */
    protected function wrapCssLink($pathToCssFile)
    {
        return "<link rel='stylesheet' type='text/css' href='{$pathToCssFile}' />";
    }
}
