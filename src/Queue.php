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
    const STATUS_ERROR = 'error';

    protected $queueDir;
    protected $url;
    protected $urlRegex;
    protected $rejectFiles = '*.png,*.jpg,*.jpeg,*.css,*.js';

    /**
     * Constructor
     *
     * @todo Swap to a more specific exception
     *
     * @param string $queueDir
     */
    public function __construct($queueDir)
    {
        $this->init($queueDir);
    }

    protected function init($queueDir)
    {
        if (!$this->isDirectory($queueDir))
        {
            throw new \Exception(
                "The supplied queue directory does not exist"
            );
        }

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
        if ($this->fileExists($this->getQueueEntryPath()))
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
        return $this->getQueueDir() . '/' . $this->getQueueEntryName();
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
        $files = glob($this->getQueueDir() . '/*. ' . self::STATUS_READY);
        $data = false;

        if ($files) {
            $file = current($files);
            $json = file_get_contents($file);
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
        $this->changeItemStatus($itemData, self::STATUS_DOING);
        $ok = $this->fetchSite($itemData);
        $this->changeItemStatus(
            $itemData,
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
        #system($command);
        trigger_error("Processing fetch", E_USER_ERROR);
        sleep(1);

        return true;
    }

    protected function changeItemStatus(array $itemData, $status)
    {
        // @todo Rename the item from current status to the new one
    }

    protected function sleep()
    {
        sleep(2);
    }

    public function getQueueDir()
    {
        return $this->queueDir;
    }

    protected function fileExists($filename)
    {
        return file_exists($filename);
    }

    protected function isDirectory($filename)
    {
        return is_dir($filename);
    }
}
