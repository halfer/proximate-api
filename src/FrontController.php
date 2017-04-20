<?php

namespace Proximate;

use Slim\App;
use Proximate\Queue\Write as Queue;
use Proximate\Service\File as FileService;
use League\Flysystem\Adapter\Local as FlyFileAdapter;
use League\Flysystem\Filesystem as FlyFilesystem;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Proximate\Storage\Filesystem as ProximateFilesystem;

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
        $filesystemAdapter = new FlyFileAdapter($this->cacheFolder);
        $filesystem = new FlyFilesystem($filesystemAdapter);
        $cacheAdapter = new ProximateFilesystem($filesystem);

        $cachePool = new FilesystemCachePool($filesystem);
        $cacheAdapter->setCacheItemPoolInterface($cachePool);

        return $cacheAdapter;
    }

    abstract function getRouting(App $app);
}
