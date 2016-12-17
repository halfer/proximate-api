<?php

/** 
 * Simple file-based queueing system
 *
 * Queue items are stored in the filing system:
 *
 * *.ready      ready to process
 * *.doing      currently working
 * *.done       finished
 * *.error      failed
 *
 * This class could in fact be split into two - QueueInsert and QueueRead,
 * that might simplify the tests too.
 */

namespace Proximate;

use Proximate\Service\File as FileService;

class Queue
{
    const STATUS_READY = 'ready';
    const STATUS_DOING = 'doing';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';

    protected $queueDir;
    protected $fileService;
    protected $url;
    protected $urlRegex;
    protected $rejectFiles = '*.png,*.jpg,*.jpeg,*.css,*.js';

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

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl()
    {
        if (!$this->url)
        {
            throw new \Exception("No URL set");
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
     * @throws \Exception
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
     * @todo Use a more specific exception
     *
     * @throws \Exception
     */
    protected function checkEntryExists()
    {
        if ($this->getFileService()->fileExists($this->getQueueEntryPath()))
        {
            throw new \Exception(
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

    /**
     * Gets an entry for the given URL and status
     *
     * @param string $url
     * @param string $status
     * @return string
     */
    protected function getQueueEntryPathForUrl($url, $status)
    {
        return $this->getQueueDir() . '/' . $this->getQueueEntryName($url, $status);
    }

    protected function getQueueEntryDetails()
    {
        return [
            'url' => $this->getUrl(),
            'url_regex' => $this->urlRegex,
            'reject_files' => $this->rejectFiles,
        ];
    }

    protected function getQueueEntryName($url, $status = self::STATUS_READY)
    {
        return $this->calculateUrlHash($url) . '.' . $status;
    }

    protected function calculateUrlHash($url)
    {
        return md5($url);
    }

    public function process($loop = 50)
    {
        for ($i = 0; $i < $loop; $i++)
        {
            if ($itemData = $this->getNextQueueItem())
            {
                $this->processQueueItem($itemData);
            }
            else
            {
                $this->sleep();
            }
        }
    }

    /**
     * Returns the data for the next ready item, if one is available
     *
     * @todo Validate the item contains the right keys
     *
     * @return string
     */
    protected function getNextQueueItem()
    {
        $fileService = $this->getFileService();
        $pattern = $this->getQueueDir() . '/*.' . self::STATUS_READY;
        $files = $fileService->glob($pattern);
        $data = false;

        if ($files) {
            $file = current($files);
            $json = $fileService->fileGetContents($file);
            $data = json_decode($json, true);

            // If the item does not contain JSON, bork
            if (!$data)
            {
                throw new \Exception(
                    "Invalid queue item found"
                );
            }
        }

        return $data;
    }

    protected function processQueueItem(array $itemData)
    {
        $url = $itemData['url'];
        $this->changeItemStatus($url, self::STATUS_READY, self::STATUS_DOING);
        $ok = $this->fetchSite($itemData);
        $this->changeItemStatus(
            $url,
            self::STATUS_DOING,
            $ok ? self::STATUS_DONE : self::STATUS_ERROR
        );
    }

    protected function fetchSite(array $itemData)
    {
        // @todo Run a wget on the site
        $command = "
            wget \
                --recursive \
                --wait 3 \
                --limit-rate=20K \
                --delete-after \
                --reject \"*.png,*.jpg,*.jpeg,*.css,*.js\" \
                --accept-regex \".*(/about/careers/.*)|(/job/.*)\" \
                -e use_proxy=yes \
                -e http_proxy=127.0.0.1:8082 \
                http://www.nimvelo.com/about/careers/
        ";

        return true;
    }

    protected function changeItemStatus($url, $oldStatus, $newStatus)
    {
        $this->getFileService()->rename(
            $this->getQueueEntryPathForUrl($url, $oldStatus),
            $this->getQueueEntryPathForUrl($url, $newStatus)
        );
    }

    protected function sleep()
    {
        sleep(2);
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
