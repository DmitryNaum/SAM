<?php

namespace Dmitrynaum\SAM;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Dmitrynaum\SAM\Component\Manifest;

/**
 * Класс для построения (компиляции) asset`ов
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class AssetBuilder
{

    /**
     * Флаг "Заморозить asset`ы"
     * @var bool
     */
    protected $needFreeze;
    
    /**
     * Флаг "Сжать asset`ы"
     * @var bool
     */
    protected $needCompress;
    
    /**
     * Manifest
     * @var Manifest
     */
    protected $manifest;
    
    /**
     * Путь до файла sam.json
     * @var string
     */
    protected $manifestFilePath;

    /**
     * @param string $manifestFilePath - Путь до файла sam.json
     */
    public function __construct($manifestFilePath)
    {
        $this->manifestFilePath = $manifestFilePath;
        $this->needCompress     = false;
        $this->needFreeze       = false;
        $this->resultMap        = [];
    }

    /**
     * Включить заморозку asset`ов
     */
    public function enableFreezing()
    {
        $this->needFreeze = true;
    }

    /**
     * Выключить заморозку asset`ов
     */
    public function disableFreezing()
    {
        $this->needFreeze = false;
    }

    /**
     * Включена ли заморозка asset`ов
     */
    public function isFreezingEnabled()
    {
        return $this->needFreeze;
    }

    /**
     * Включить сжатие asset`ов
     */
    public function enableCompressor()
    {
        $this->needCompress = true;
    }

    /**
     * Выключить сжатие asset`ов
     */
    public function disableCompressor()
    {
        $this->needCompress = false;
    }

    /**
     * Включиено ли сжатие asset`ов
     */
    public function isCompressorEnabled()
    {
        return $this->needCompress;
    }

    /**
     * Собрать asset`ы (скомпилировать)
     */
    public function build()
    {
        $this->clearAssetDirectory();
        $this->buildCss();
        $this->buildJs();
        $this->resultMap()->save();
    }

    /**
     * Очистить папку с asset`ами
     */
    protected function clearAssetDirectory()
    {
        // Получаем путь до папки с скомпилированными asset`ами
        $assetBasePath = $this->manifest()->getAssetBasePath();
        
        // Получием список всех вложенных папок и объектов
        $dirIterator   = new RecursiveDirectoryIterator($assetBasePath, RecursiveDirectoryIterator::SKIP_DOTS);
        $files         = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);
        
        // Удаляем все вложенные файлы и объекты
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
    }

    /**
     * Скомпилировать css asset`ы
     */
    protected function buildCss()
    {
        $cssAssets = $this->manifest()->getCssAssets();

        $this->buildAssets($cssAssets);
    }

    /**
     * Скомпилировать JavaScript asset`ы
     */
    protected function buildJs()
    {
        $jsAssets = $this->manifest()->getJsAssets();

        $this->buildAssets($jsAssets);
    }

    /**
     * Скомпилировать asset`ы
     * @param strin[] $assets Список asset`ов для компиляции
     */
    protected function buildAssets($assets)
    {
        // Обходим все asset`ы
        foreach ($assets as $assetName => $assetFiles) {
            // Считываем содержимое файлов которые необходимо объежинить в asset
            $assetContent = $this->readFiles($assetFiles);

            // Вычисляем путь до файла куда будет сохранен asset
            $assetPath    = $this->manifest()->getAssetBasePath() . '/' . $assetName;
            
            // Если необходимо сжать asset`ы
            if ($this->isCompressorEnabled()) {
                // Получаем объект для минимфикации
                $compressor = $this->getCompressor($assetName);
                
                // Сообщаем ему что необходимо минимфицировать
                $compressor->add($assetContent);
                
                // Минифицируем ассет
                $assetContent = $compressor->minify();
            }

            // Если необходимо заморозить
            if ($this->isFreezingEnabled()) {
                // Вычисляем путь до файла в который будет сохранен asset
                $assetHash = sha1($assetContent);
                $assetPath = $this->getFreezingAssetFileName($assetName, $assetHash);
            }
            
            // Сохраняем asset
            file_put_contents($assetPath, $assetContent);

            // Добавляем путь до asset файла в карту asset`ов
            $this->resultMap()->addAsset($assetName, $assetPath);
        }
    }

    /**
     * Получить имя замороженного файла asset`а
     * @param string $assetName - Имя asset`а
     * @param string $assetHash - Хэш asset`а
     * @return string
     */
    protected function getFreezingAssetFileName($assetName, $assetHash)
    {
        $fileInfo = pathinfo($assetName);
        $fileName = $fileInfo['filename'] . '-' . $assetHash . '.' . $fileInfo['extension'];

        return $this->manifest()->getAssetBasePath() . '/' . $fileInfo['dirname'] . '/' . $fileName;
    }

    /**
     * Получить Manifest
     * @return Manifest
     */
    protected function manifest()
    {
        if (!$this->manifest) {
            $this->manifest = new Manifest($this->manifestFilePath);
        }

        return $this->manifest;
    }

    /**
     * Получить карту asset`ов
     * @return \Dmitrynaum\SAM\Component\AssetMap
     */
    protected function resultMap()
    {
        return $this->manifest()->resultMap();
    }

    /**
     * Считать файлы и объединить
     * @param string[] $filePaths - список файлов
     * @return string
     */
    protected function readFiles($filePaths)
    {
        $filesContent = [];

        foreach ($filePaths as $filePath) {
            $filesContent[] = file_get_contents($filePath);
        }

        return join(PHP_EOL, $filesContent);
    }

    /**
     * Получить компессор asset`ов в зависимости от типа asset`а (css или js)
     * @param string $assetPath - имя asset`а
     * @return \MatthiasMullie\Minify\Minify
     */
    protected function getCompressor($assetPath)
    {
        $fileExtension = pathinfo($assetPath, PATHINFO_EXTENSION);
        $compressor    = null;

        switch ($fileExtension) {
            case 'js':
                $compressor = new \MatthiasMullie\Minify\JS();
                break;
            case 'css':
                $compressor = new \MatthiasMullie\Minify\CSS();
                break;
        }

        return $compressor;
    }
}
