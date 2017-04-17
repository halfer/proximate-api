<?php

use Proximate\Routing\Routing;
use League\Flysystem\Adapter\Local as FlyFileAdapter;
use League\Flysystem\Filesystem as FlyFilesystem;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Proximate\CacheAdapter\Filesystem as ProximateFilesystem;

$root = realpath(__DIR__ . '/..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

class TestFrontController extends \Proximate\FrontController
{
    public function getRouting(\Slim\App $app)
    {
        return new Routing($app);
    }

    public function getCacheAdapter()
    {
        $filesystemAdapter = new FlyFileAdapter('/remote');
        $filesystem = new FlyFilesystem($filesystemAdapter);
        $cacheAdapter = new ProximateFilesystem($filesystem);

        $cachePool = new FilesystemCachePool($filesystem);
        $cacheAdapter->setCacheItemPoolInterface($cachePool);

        return $cacheAdapter;
    }

    public function getRecorderCurl()
    {
        return new PestJSON('http://proximate-proxy:8081');
    }

    public function getPlaybackCurl()
    {
        return new PestJSON('http://proximate-proxy:8082');
    }
}

$frontController = new TestFrontController('/var/proximate/queue');
$frontController->execute();
