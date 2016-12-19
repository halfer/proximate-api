<?php

/**
 * Class to write new entries to the queue
 */

namespace Proximate\Queue;

use Proximate\Exception\AlreadyQueued as AlreadyQueuedException;
use Proximate\Exception\RequiredParam as RequiredParamException;

class Write extends Base
{
    const DEFAULT_REJECT_FILES = '*.png,*.jpg,*.jpeg,*.css,*.js';

    protected $url;
    protected $urlRegex;
    protected $rejectFiles = self::DEFAULT_REJECT_FILES;

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets the currently set URL string
     *
     * @return string
     * @throws \Exception
     */
    public function getUrl()
    {
        if (!$this->url)
        {
            throw new RequiredParamException("No URL set");
        }

        return $this->url;
    }

    public function setUrlRegex($urlRegex)
    {
        $this->urlRegex = $urlRegex;

        return $this;
    }

    public function getUrlRegex()
    {
        return $this->urlRegex;
    }

    public function setRejectFiles($rejectFiles)
    {
        $this->rejectFiles = $rejectFiles;

        return $this;
    }

    public function getRejectFiles()
    {
        return $this->rejectFiles;
    }

    /**
     * Creates a queue item for the current URL
     *
     * @throws AlreadyQueuedException
     */
    public function queue()
    {
        $this->checkEntryExists();
        $ok = $this->createQueueEntry();

        return $ok;
    }

    /**
     * Checks to see if the current URL is currently queued already
     *
     * @throws AlreadyQueuedException
     */
    protected function checkEntryExists()
    {
        if ($this->getFileService()->fileExists($this->getQueueEntryPath()))
        {
            throw new AlreadyQueuedException(
                "This URL is already queued"
            );
        }
    }

    protected function createQueueEntry()
    {
        $bytes = $this->getFileService()->filePutContents(
            $this->getQueueEntryPath(),
            json_encode($this->getQueueEntryDetails(), JSON_PRETTY_PRINT)
        );

        return (bool) $bytes;
    }

    /**
     * Gets the "ready" entry for current URL
     *
     * @return string
     */
    protected function getQueueEntryPath()
    {
        return $this->getQueueDir() . '/' . $this->getQueueEntryName($this->url);
    }

    protected function getQueueEntryDetails()
    {
        return [
            'url' => $this->getUrl(),
            'url_regex' => $this->urlRegex,
            'reject_files' => $this->rejectFiles,
        ];
    }
}