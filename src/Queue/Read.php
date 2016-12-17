<?php

/**
 * Class to read entries from the queue
 */

namespace Proximate\Queue;

class Read extends Base
{
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
