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
     * @var string
     */
    protected $js = [];
    
    /**
     * Список ссылок на удаленные JavaScript файлы
     * @var string[]
     */
    protected $remoteJs = [];

    /**
     * Список используемых Css asset`ов
     * @var string
     */
    protected $css = [];
    
    /**
     * Список ссылок на удаленные css файлы
     * @var string[]
     */
    protected $remoteCss = [];

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
     * Host на котором висит сервер
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
        $this->js[] = $assetName;
    }

    /**
     * Использовать CSS Asset
     * @param string $assetName - имя ассета
     */
    public function useCss($assetName)
    {
        $this->css[] = $assetName;
    }
    
    /**
     * Использовать удаленный css.
     * Ссылка просто оборачивается в тег link
     * @param string $cssUrl ссылка на css файл
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
     * Получить html теги script с используемыми JavaScript asset`ами
     * @return string
     */
    public function renderJs()
    {
        $jsUrls = $this->remoteJs;
        
        foreach ($this->js as $assetName) {
            $jsUrls[] = $this->getAssetUrl($assetName);
        }
        
        $jsTags = '';
        foreach ($jsUrls as $jsUrl) {
            $jsTags .= $this->wrapJsLink($jsUrl);
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
     * Получить ссылку на asset по его имени
     * @param sring $assetName название asset`а
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
     * @return string
     */
    protected function wrapJsLink($pathToJsFile)
    {
        return "<script src='{$pathToJsFile}'></script>";
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
