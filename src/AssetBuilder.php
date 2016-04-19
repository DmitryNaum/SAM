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
        // Получаем путь до папки с скомпилированными asset`ами относительно корня проекта
        $assetBasePath = $this->manifest()->getAssetBasePathFromProjectRoot();
        
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
     * @param string[] $assets Список asset`ов для компиляции
     */
    protected function buildAssets($assets)
    {
        // Получаем карту ассетов [имя_ассета => [список файлов]]
        $assetsPaths = $this->getFilesPaths($assets);
        
        // Обходим все asset`ы
        foreach ($assetsPaths as $assetName => $assetFiles) {
            // Считываем содержимое файлов которые необходимо объежинить в asset
            $assetContent = $this->readFiles($assetFiles);

            // Вычисляем путь до файла куда будет сохранен asset
            $assetSavePath = $this->manifest()->getAssetBasePathFromProjectRoot() . '/' . $assetName;
            $assetWebPath  = $this->manifest()->getAssetBasePath() . '/' . $assetName;
            
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
                $assetSavePath = $this->getFreezingAssetFileName($assetName, $assetHash);
            }
            
            // Сохраняем asset
            $this->saveAsset($assetSavePath, $assetContent);

            // Добавляем путь до asset файла в карту asset`ов
            $this->resultMap()->addAsset($assetName, $assetWebPath);
        }
    }
    
    /**
     * Сохранить ассет в файл
     * @param string $assetPath путь до файла
     * @param string $content контент
     */
    protected function saveAsset($assetPath, $content)
    {
        $dirName = dirname($assetPath);
        
        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }
        
        file_put_contents($assetPath, $content);
    }
    
    /**
     * Получить список asset`ов и файлы которые в них используются
     * @param array $assets
     * @return array список файлов используемых в ассетах
     */
    protected function getFilesPaths($assets)
    {
        $assetFilePaths = [];
        foreach ($assets as $assetName => $assetFiles) {
            $assetFilePaths[$assetName] = $this->resolveAssetFilesPaths($assets, $assetFiles);
        }
        
        return $assetFilePaths;
    }
    
    /**
     * Получить список файлов ассета
     * @param array $assets
     * @param array $assetFiles
     * @return array
     */
    protected function resolveAssetFilesPaths($assets, $assetFiles)
    {
        $assetFilePaths = [];
        // Обходим все файлы
        foreach ($assetFiles as $assetNameOrFile) {
            // Если в списке ассетов есть ассет с именем $assetNameOrFile
            if (isset($assets[$assetNameOrFile])) {
                // это имя ассета файлы которого необходимо использовать
                $assetFiles     = $assets[$assetNameOrFile];
                $assetFilePaths = array_merge($assetFilePaths, $this->resolveAssetFilesPaths($assets, $assetFiles));
            } else {
                // это путь до файла который необходимо использовать
                $assetFilePaths[] = $assetNameOrFile;
            }
        }

        return $assetFilePaths;
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

        return $this->manifest()->getAssetBasePathFromProjectRoot() . '/' . $fileInfo['dirname'] . '/' . $fileName;
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
