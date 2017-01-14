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
        $this->init($fileService, $recordCachePath, $playCachePath);
    }

    public function init(File $fileService, $recordCachePath, $playCachePath)
    {
        $this->fileService = $fileService;
        $this->recordCachePath = $recordCachePath;
        $this->playCachePath = $playCachePath;
    }

    public function execute()
    {
        $this->validatePaths();
        $this->findFilesAndProcess();
    }

    protected function findFilesAndProcess()
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

    /**
     * Accepts a path to a folder, e.g. /remote/cache/record/www_example_com for processing
     *
     * @param string $urlFolder
     */
    protected function processFolder($urlFolder)
    {
        $this->copyFiles($urlFolder);
        $this->copyMappings($urlFolder);
    }

    protected function copyFiles($urlFolder)
    {
        $files = getFilesFolder($urlFolder);
        system("cp {$files}/* /remote/cache/playback/__files");
    }

    /**
     * Copies all JSON mappings
     *
     * @param string $urlFolder
     */
    protected function copyMappings($urlFolder)
    {
        $mappingsFiles = glob(getMappingsFolder($urlFolder) . '/*');
        foreach ($mappingsFiles as $mappingFile)
        {
            copyMapping($urlFolder, $mappingFile);
        }
    }

    /**
     * Copies a single JSON mapping file and adds a header
     *
     * @param string $mappingFile
     */
    function copyMapping($urlFolder, $mappingFile)
    {
        // Read the data from the mapping file
        $json = file_get_contents($mappingFile);
        $data = json_decode($json, true);

        // Add in a host header
        $data['request']['headers'] = [
            'Host' => ['equalTo' => getSiteHost($urlFolder), ]
        ];

        $leafName = md5($json) . '.json';
        $jsonAgain = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents('/remote/cache/playback/mappings/' . $leafName, $jsonAgain);
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
