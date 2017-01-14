<?php

/* 
 * Service to move cache items from the recorder to the player
 */

namespace Proximate\Service;

use Proximate\Exception\DirectoryNotFound as DirectoryNotFoundException;

class CacheCopier
{
    protected $fileService;
    protected $recordCachePath;
    protected $playCachePath;

    public function __construct(File $fileService, $recordCachePath, $playCachePath)
    {
        $this->fileService = $fileService;
        $this->recordCachePath = $recordCachePath;
        $this->playCachePath = $playCachePath;
    }

    public function execute()
    {
        $this->validatePaths();
        $this->copyCache();
    }

    protected function copyCache()
    {
        $folders = $this->getFileService()->glob($this->recordCachePath . '/*');
        foreach ($folders as $urlFolder)
        {
            if ($this->checkFolder($urlFolder))
            {
                $this->processFolder($urlFolder);
            }
        }
    }

    protected function validatePaths()
    {
        $this->validatePath($this->recordCachePath);
        $this->validatePath($this->playCachePath);
    }

    public function validatePath($path)
    {
        if (!$this->getFileService()->isDirectory($path))
        {
            throw new DirectoryNotFoundException(
                sprintf("Cache directory `%s` does not exist", $path)
            );
        }
    }

    /**
     * Checks if a path to a folder, e.g. /remote/cache/record/www_example_com can be processed
     *
     * @param string $urlFolder
     */
    protected function checkFolder($urlFolder)
    {
        $fileService = $this->getFileService();

        return
            $fileService->isDirectory($urlFolder) &&
            $fileService->isDirectory($this->getMappingsFolder($urlFolder)) &&
            $fileService->isDirectory($this->getFilesFolder($urlFolder));
    }

    protected function getMappingsFolder($urlFolder)
    {
        return $urlFolder . '/mappings';
    }

    protected function getFilesFolder($urlFolder)
    {
        return $urlFolder . '/__files';
    }

    /**
     * Gets the file service class
     *
     * @return File
     */
    protected function getFileService()
    {
        return $this->fileService;
    }
}
