<?php

/**
 * Class to set up routing rules
 *
 * @todo Add setters or a DIC for hardwired items in this file:
 *
 * - proxy log
 * - file service
 * - crawler queue path
 */

namespace Proximate\Routing;

use Proximate\Storage\BaseAdapter;
use Slim\Http\Request;
use Slim\Http\Response;
use Proximate\Service\File as FileService;

class Routing
{
    protected $app;
    protected $cacheAdapter;
    protected $queue;

    public function __construct(\Slim\App $app)
    {
        $this->app = $app;
    }

    public function setCacheAdapter(BaseAdapter $cacheAdapter)
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
         * Handles both the fetch and the delete endpoints
         */
        $app->map(['GET', 'DELETE'],'/cache/{guid}', function($request, $response, $args) use ($routing, $cacheAdapter) {
            $controller = $request->getMethod() == 'GET' ?
                $routing->getItemGetController($request, $response) :
                $routing->getItemDeleteController($request, $response);
            $controller->setCacheAdapter($cacheAdapter);
            $controller->setGuid($args['guid']);
            return $controller->execute();
        });

        /**
         * Requests that a specific site is cached
         *
         * Adds the item onto the queueing system and returns a unique GUID
         *
         * Takes a JSON input document containing:
         *
         * [url, path_regex]
         */
        $app->post('/cache', function ($request, $response) use ($queue, $routing) {
            $controller = $routing->getCacheSaveController($request, $response);
            $controller->setQueue($queue);
            return $controller->execute();
        });

        $app->get('/log', function ($request, $response) use ($routing) {
            $controller = $routing->getProxyLogController($request, $response);
            $controller->setLogPath('/remote/cache/proxy.log');
            return $controller->execute();
        });

        $app->get('/queue/{status}', function ($request, $response, $args) use ($routing) {
            $controller = $routing->getQueueListController($request, $response);
            $controller->setQueuePath('/var/proximate/queue');
            $controller->setFileService(new FileService());
            $controller->setStatus($args['status']);
            return $controller->execute();
        });

        // Set up 404 JSON response
        $container = $app->getContainer();
        $container['notFoundHandler'] = function() {
            return function(Request $request, Response $response) {
                $result = [
                    'result' => [
                        'ok' => false,
                        'error' => 'Endpoint not found'
                    ]
                ];
                return
                    $response->
                    withStatus(404)->
                    withHeader('Content-Type', 'application/json')->
                    write(json_encode($result));
            };
        };
    }

    /**
     * @return \Proximate\Controller\ItemGet
     */
    protected function getItemGetController($request, $response)
    {
        return new \Proximate\Controller\ItemGet($request, $response);
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

    protected function getProxyLogController($request, $response)
    {
        return new \Proximate\Controller\ProxyLog($request, $response);
    }

    protected function getQueueListController($request, $response)
    {
        return new \Proximate\Controller\QueueList($request, $response);
    }
}
