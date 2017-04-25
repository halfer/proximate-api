<?php

namespace Proximate;

use Slim\App;
use Proximate\Queue\Write as Queue;
use Proximate\Service\File as FileService;
use Proximate\Storage\FilecacheFactory;

/**
 * Sets up front controller based on preferences in a child class
 */

abstract class FrontController
{
    protected $queueFolder;
    protected $cacheFolder;

    public function __construct($queueFolder, $cacheFolder)
    {
        $this->queueFolder = $queueFolder;
        $this->cacheFolder = $cacheFolder;
    }

    public function execute()
    {
        $app = new App();
        $queue = new Queue($this->queueFolder, new FileService());

        // Set up routing object
        $routing = $this->getRouting($app);
        $routing->setCacheAdapter($this->getCacheAdapter());
        $routing->setQueue($queue);
        $routing->execute();

        $app->run();
    }

    public function getCacheAdapter()
    {
        $factory = new FilecacheFactory($this->cacheFolder);
        $factory->init();

        return $factory->getCacheAdapter();
    }

    abstract function getRouting(App $app);
}
