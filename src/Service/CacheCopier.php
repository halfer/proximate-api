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
        //$this->copy();
    }

    protected function copy()
    {
        $folders = glob($pathRecord . '/*');
        foreach ($folders as $urlFolder)
        {
            if (checkFolder($urlFolder))
            {
                processFolder($urlFolder);
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
     * Gets the file service class
     *
     * @return File
     */
    protected function getFileService()
    {
        return $this->fileService;
    }
}
