<?php

/**
 * Common base for all queue classes
 */

namespace Proximate\Queue;

use Proximate\Service\File as FileService;

class Base
{
    const STATUS_READY = 'ready';
    const STATUS_DOING = 'doing';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';

    protected $queueDir;
    protected $fileService;

    /**
     * Constructor
     *
     * @param string $queueDir
     * @param FileService $fileService
     */
    public function __construct($queueDir, FileService $fileService)
    {
        $this->init($queueDir, $fileService);
    }

    /**
     * Mockable version of the c'tor
     *
     * @todo Swap to a more specific exception
     *
     * @param string $queueDir
     * @param FileService $fileService
     * @throws \Exception
     */
    protected function init($queueDir, FileService $fileService)
    {
        if (!$fileService->isDirectory($queueDir))
        {
            throw new \Exception(
                "The supplied queue directory does not exist"
            );
        }

        $this->queueDir = $queueDir;
        $this->fileService = $fileService;
    }

    protected function getQueueEntryName($url, $status = self::STATUS_READY)
    {
        return $this->calculateUrlHash($url) . '.' . $status;
    }

    protected function calculateUrlHash($url)
    {
        return md5($url);
    }

    public function getQueueDir()
    {
        return $this->queueDir;
    }

    /**
     * Returns the current file service injected into the queue
     *
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->fileService;
    }
}
