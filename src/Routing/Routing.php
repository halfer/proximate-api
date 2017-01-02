<?php

/**
 * Class to set up routing rules
 */

namespace Proximate\Routing;

class Routing
{
    protected $app;
    protected $curl;
    protected $queue;

    public function __construct(\Slim\App $app)
    {
        $this->app = $app;
    }

    public function setCurl(\PestJSON $curl)
    {
        $this->curl = $curl;
    }

    public function setQueue(\Proximate\Queue\Write $queue)
    {
        $this->queue = $queue;
    }

    public function execute()
    {
        // Set up the dependencies
        $app = $this->app;
        $curl = $this->curl;
        $queue = $this->queue;

        /**
 * Counts the number of pages stored in the cache
 */
        $app->get('/count', function ($request, $response) use ($curl) {
            $controller = new \Proximate\Controller\Count($request, $response);
            $controller->setCurl($curl);
            return $controller->execute();
        });

        /**
         * Counts the number of pages for a specific domain in the cache
         */
        $app->get('/count/{url}', function ($request, $response, $args) use ($curl) {
            $controller = new \Proximate\Controller\CountUrl($request, $response);
            $controller->setCurl($curl);
            $controller->setUrl($args['url']);
            return $controller->execute();
        });

        /**
         * List the pages in the cache, given a page and a page size
         *
         * This can use GET "/__admin/mappings" from the WireMock playback instance
         */
        $app->get('/list/{page}/[{pagesize}]', function ($request, $response) {
            $controller = new \Proximate\Controller\CacheList($request, $response);
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
        $app->post('/cache', function ($request, $response) use ($queue) {
            $controller = new \Proximate\Controller\CacheSave($request, $response);
            $controller->setQueue($queue);
            return $controller->execute();
        });

        /**
         * Fetches the status of a specific site fetch
         *
         * @todo I think I should just use the URL rather than a GUID, since duplicates won't
         * be separately cached.
         */
        $app->get('/status/{guid}', function ($request, $response, $args) {
            $controller = new \Proximate\Controller\ItemStatus($request, $response);
            return $controller->execute();
        });

        /**
         * Requests that a specific site is deleted from the cache
         *
         * This can use DELETE "/__admin/mappings/{stubMappingId}" from the WireMock playback instance
         */
        $app->delete('/cache/{url}', function ($request, $response, $args) {
            $response->write("Delete the specified page");
            return $response;
        });
    }
}