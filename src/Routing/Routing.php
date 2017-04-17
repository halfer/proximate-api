<?php

/**
 * Class to set up routing rules
 */

namespace Proximate\Routing;

use Slim\Http\Request;
use Slim\Http\Response;

class Routing
{
    protected $app;
    protected $cacheAdapter;
    protected $queue;

    public function __construct(\Slim\App $app)
    {
        $this->app = $app;
    }

    public function setCacheAdapter(\Proximate\CacheAdapter\BaseAdapter $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
    }

    public function setQueue(\Proximate\Queue\Write $queue)
    {
        $this->queue = $queue;
    }

    public function execute()
    {
        // Set up the dependencies
        $app = $this->app;
        $cacheAdapter = $this->cacheAdapter;
        $queue = $this->queue;
        $routing = $this;

        /**
         * Counts the number of pages stored in the cache
         */
        $app->get('/count', function ($request, $response) use ($routing, $cacheAdapter) {
            $controller = $routing->getCountController($request, $response);
            $controller->setCacheAdapter($cacheAdapter);
            return $controller->execute();
        });

        $app->get('/list[/{page}[/{pagesize}]]', function($request, $response, $args) use ($routing, $cacheAdapter) {
            $controller = $routing->getCacheListController($request, $response);
            $controller->setCacheAdapter($cacheAdapter);
            $controller->setPage(isset($args['page']) ? $args['page'] : 1);
            $controller->setPageSize(isset($args['pagesize']) ? $args['pagesize'] : 10);
            return $controller->execute();
        });

        /**
         * Requests that a specific site is cached
         *
         * Adds the item onto the queueing system and returns a unique GUID
         *
         * Takes a JSON input document containing:
         *
         * [url, url_regex, reject_files]
         */
        $app->post('/cache', function ($request, $response) use ($queue, $routing) {
            $controller = $routing->getCacheSaveController($request, $response);
            $controller->setQueue($queue);
            return $controller->execute();
        });

        /**
         * Requests that a specific mapping is deleted from the cache
         */
        $app->delete('/cache/{guid}', function ($request, $response, $args) use ($routing, $cacheAdapter) {
            $controller = $routing->getItemDeleteController($request, $response);
            $controller->setCacheAdapter($cacheAdapter);
            $controller->setGuid($args['guid']);
            return $controller->execute();
        });
    }

    protected function getCountController($request, $response)
    {
        return new \Proximate\Controller\Count($request, $response);
    }

    protected function getCacheListController($request, $response)
    {
        return new \Proximate\Controller\CacheList($request, $response);
    }

    protected function getCacheSaveController($request, $response)
    {
        return new \Proximate\Controller\CacheSave($request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return \Proximate\Controller\ItemDelete
     */
    protected function getItemDeleteController($request, $response)
    {
        return new \Proximate\Controller\ItemDelete($request, $response);
    }
}
