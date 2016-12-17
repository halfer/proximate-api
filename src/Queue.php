<?php

/** 
 * Simple file-based queueing system
 */

namespace Proximate;

class Queue
{
    protected $inDir;
    protected $outDir;
    protected $rejectFiles = '*.png,*.jpg,*.jpeg,*.css,*.js';

    // @todo Might use an extension for status instead of in/out dirs
    public function __construct($inDir, $outDir)
    {
        $this->inDir = $inDir;
        $this->outDir = $outDir;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function setUrlRegex($urlRegex)
    {
        $this->urlRegex = $urlRegex;

        return $this;
    }

    public function setRejectFiles($rejectFiles)
    {
        $this->rejectFiles = $rejectFiles;

        return $this;
    }

    /**
     * Creates a queue item for the current URL
     */
    public function queue()
    {
    }

    public function processor($loop = 50)
    {
        for ($i = 0; $i < $loop; $i++)
        {
            if ($queueItem = $this->getNextQueueItem())
            {
                $this->processQueueItem($queueItem);
            }
            else
            {
                $this->sleep();
            }
        }
    }

    protected function getNextQueueItem()
    {
        // @todo
        return false;
    }

    protected function processQueueItem($queueItem)
    {
        $this->fetchSite($queueItem);
        $this->moveQueueItem($queueItem);
    }

    protected function fetchSite($queueItem)
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
        system($command);
    }

    protected function moveQueueItem($queueItem)
    {
        // @todo
    }

    protected function sleep()
    {
        sleep(2);
    }
}
