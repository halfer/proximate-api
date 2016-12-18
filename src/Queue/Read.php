<?php

/**
 * Class to read entries from the queue
 */

namespace Proximate\Queue;

use Proximate\Service\File as FileService;
use Proximate\Service\SiteFetcher as FetcherService;

class Read extends Base
{
    protected $fetcherService;

    public function __construct($queueDir, FileService $fileService, FetcherService $fetcherService)
    {
        parent::__construct($queueDir, $fileService);
        $this->initFetcher($fetcherService);
    }

    protected function initFetcher(FetcherService $fetcherService)
    {
        $this->fetcherService = $fetcherService;
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
        // Call the site fetcher service here
        $this->getSiteFetcherService()->execute(
            $itemData['url'],
            $itemData['url_regex'],
            $itemData['reject_files']
        );
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

    /**
     * Gets the currently configured site fetcher
     *
     * @return FetcherService
     */
    protected function getSiteFetcherService()
    {
        return $this->fetcherService;
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
}
