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
 */

namespace Proximate;

class Queue
{
    const STATUS_READY = 'ready';
    const STATUS_DOING = 'doing';
    const STATUS_DONE = 'done';
    // Not sure if I need this one yet
    #const STATUS_ERROR = 'error';

    protected $queueDir;
    protected $url;
    protected $urlRegex;
    protected $rejectFiles = '*.png,*.jpg,*.jpeg,*.css,*.js';

    /**
     * Constructor
     *
     * @param string $queueDir
     */
    public function __construct($queueDir)
    {
        $this->queueDir = $queueDir;
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

    public function setRejectFiles($rejectFiles)
    {
        $this->rejectFiles = $rejectFiles;

        return $this;
    }

    /**
     * Creates a queue item for the current URL
     *
     * @throws \Exception
     */
    public function queue()
    {
        $this->checkEntryExists();
        $this->createQueueEntry();
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
        if (file_exists($this->getQueueEntryPath()))
        {
            throw new \Exception(
                "This URL is already queued"
            );
        }
    }

    protected function createQueueEntry()
    {
        $bytes = file_put_contents(
            $this->getQueueEntryPath(),
            json_encode($this->getQueueEntryDetails(), JSON_PRETTY_PRINT)
        );

        return (bool) $bytes;
    }

    protected function getQueueEntryPath()
    {
        return $this->queueDir . '/' . $this->getQueueEntryName();
    }

    protected function getQueueEntryDetails()
    {
        return [
            'url' => $this->getUrl(),
            'url_regex' => $this->urlRegex,
            'reject_files' => $this->rejectFiles,
        ];
    }

    protected function getQueueEntryName()
    {
        return $this->calculateUrlHash() . '.ready';
    }

    protected function calculateUrlHash()
    {
        return md5($this->url);
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
        $this->changeItemStatus($queueItem, 'doing');
        $this->fetchSite($queueItem);
        $this->changeItemStatus($queueItem, 'done');
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
        #system($command);
    }

    protected function changeItemStatus($queueItem, $status)
    {
        // @todo Rename the item from current status to the new one
    }

    protected function sleep()
    {
        sleep(2);
    }
}
