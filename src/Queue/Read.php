<?php

/**
 * Class to read entries from the queue
 */

namespace Proximate\Queue;

use Proximate\Service\SiteFetcher as FetcherService;
use Proximate\Service\ProxyReset as ProxyResetService;
use Proximate\Exception\RequiredDependency as RequiredDependencyException;

class Read extends Base
{
    protected $fetcherService;
    protected $proxyResetService;

    public function setFetcher(FetcherService $fetcherService)
    {
        $this->fetcherService = $fetcherService;

        return $this;
    }

    public function setProxyResetter(ProxyResetService $proxyResetService)
    {
        $this->proxyResetService = $proxyResetService;

        return $this;
    }

    public function process($loop = 50)
    {
        for ($i = 0; $i < $loop; $i++)
        {
            $this->singleIteration();
        }
    }

    protected function singleIteration()
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

    /**
     * Returns the data for the next ready item, if one is available
     *
     * Fails silently if an entry is invalid (renames it out of the way)
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

            // If the item does not contain JSON, rename it
            if (!$data)
            {
                // Derive the invalid name first
                $newName = preg_replace(
                    '#\.' . self::STATUS_READY . '$#',
                    '.' . self::STATUS_INVALID,
                    $file
                );
                $fileService->rename($file, $newName);
            }
        }

        return $data;
    }

    /**
     * @todo In the case of error it would be nice to send the error to changeItemStatus,
     * which would write it into the JSON queue item if possible
     * @param array $itemData
     */
    protected function processQueueItem(array $itemData)
    {
        $url = $itemData['url'];
        $this->changeItemStatus($url, self::STATUS_READY, self::STATUS_DOING);

        try
        {
            $this->resetProxy($url);
            $this->fetchSite($itemData);
            $status = self::STATUS_DONE;
        }
        catch (\Exception $e)
        {
            $status = self::STATUS_ERROR;
        }

        $this->changeItemStatus($url, self::STATUS_DOING, $status);
    }

    /**
     * Restarts the proxy recorder, asking it to record at this URL
     *
     * @param string $url
     */
    protected function resetProxy($url)
    {
        $this->getProxyResetterService()->execute(
            $this->getDomainForUrl($url)
        );
        $this->resetProxySleep();
    }

    /**
     * Gets the base domain for the specified URL
     *
     * @param string $url
     * @return string
     */
    protected function getDomainForUrl($url)
    {
        // Get the base domain from the URL
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        if ($scheme && $host)
        {
            $domain = $scheme . '://' . $host;
            if ($port = parse_url($url, PHP_URL_PORT))
            {
                $domain .= ':' . $port;
            }
        }

        return $domain;
    }

    protected function resetProxySleep()
    {
        sleep(5);
    }

    protected function fetchSite(array $itemData)
    {
        // Call the site fetcher service here
        $this->getSiteFetcherService()->execute(
            $itemData['url'],
            $itemData['url_regex'],
            $itemData['reject_files']
        );
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
     * @throws RequiredDependencyException
     * @return FetcherService
     */
    protected function getSiteFetcherService()
    {
        if (!$this->fetcherService)
        {
            throw new RequiredDependencyException(
                "The queue read module needs a site fetcher to operate"
            );
        }

        return $this->fetcherService;
    }

    /**
     * Gets the currently configured proxy resetter
     *
     * @throws RequiredDependencyException
     * @return ProxyResetService
     */
    protected function getProxyResetterService()
    {
        if (!$this->proxyResetService)
        {
            throw new RequiredDependencyException(
                "The queue read module needs a proxy resetter to operate"
            );
        }

        return $this->proxyResetService;
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
