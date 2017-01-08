<?php

namespace Proximate;

use Slim\App;
use Proximate\Queue\Write as Queue;
use Proximate\Service\File as FileService;

/**
 * Sets up front controller based on preferences in a child class
 */

abstract class FrontController
{
    protected $queueFolder;

    public function __construct($queueFolder)
    {
        $this->queueFolder = $queueFolder;
    }

    public function execute()
    {
        $app = new App();
        $queue = new Queue($this->queueFolder, new FileService());

        // Set up routing object
        $routing = $this->getRouting($app);
        $routing->setRecorderCurl($this->getRecorderCurl());
        $routing->setPlaybackCurl($this->getPlaybackCurl());
        $routing->setQueue($queue);
        $routing->execute();

        $app->run();
    }

    abstract function getRouting(App $app);
    abstract function getRecorderCurl();
    abstract function getPlaybackCurl();
}
