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
    protected $curlRecorder;
    protected $curlPlayback;
    protected $queue;

    public function __construct(\Slim\App $app)
    {
        $this->app = $app;
    }

    public function setRecorderCurl(\PestJSON $curl)
    {
        $this->curlRecorder = $curl;
    }

    public function setPlaybackCurl(\PestJSON $curl)
    {
        $this->curlPlayback = $curl;
    }

    public function setQueue(\Proximate\Queue\Write $queue)
    {
        $this->queue = $queue;
    }

    public function execute()
    {
        // Set up the dependencies
        $app = $this->app;
        $curlRecorder = $this->curlRecorder;
        $curlPlayback = $this->curlPlayback;
        $queue = $this->queue;
        $routing = $this;

        // Originally the plan was to offer play and record features of everything, but
        // since the record instance of WireMock will only be pointing to one subdomain's
        // folder, this isn't very useful.

        /**
 * Counts the number of pages stored in the cache
 */
        $app->get('/count', function ($request, $response) use ($curlPlayback, $routing) {
            $controller = $routing->getCountController($request, $response);
            $controller->setCurl($curlPlayback);
            return $controller->execute();
        });

        /**
         * Counts the number of pages for a specific domain in the cache
         */
        $app->get('/count/{url}', function ($request, $response, $args) use ($curlPlayback, $routing) {
            $controller = $routing->getCountUrlController($request, $response);
            $controller->setCurl($curlPlayback);
            $controller->setUrl($args['url']);
            return $controller->execute();
        });

        // The /play and /record groups here seem to be a bit out of sync with the /count
        // endpoints, and I am not sure of the value of seeing what is in the recorder, since
        // that is ephemeral. I may dump the recorder list endpoint, will see.

        $app->group('/play', function() use ($app, $curlPlayback, $routing) {
            $app->get('/list[/{page}[/{pagesize}]]', function ($request, $response, $args) use ($curlPlayback, $routing) {
                return $routing->executeList($request, $response, $args, $curlPlayback);
            });
        });

        $app->group('/record', function() use ($app, $curlRecorder, $routing) {
            $app->get('/list[/{page}[/{pagesize}]]', function ($request, $response, $args) use ($curlRecorder, $routing) {
                return $routing->executeList($request, $response, $args, $curlRecorder);
            });
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

        $app->get('/status', function(Request $request, Response $response) use ($routing) {
            $controller = $routing->getStatusController($request, $response);
            $controller->setFileService(new \Proximate\Service\File());
            return $controller->execute();
        });

        /**
         * Fetches the status of a specific site fetch
         *
         * @todo I think I should just use the URL rather than a GUID, since duplicates won't
         * be separately cached.
         */
        $app->get('/status/{guid}', function ($request, $response, $args) use ($curlPlayback, $routing) {
            $controller = $routing->getItemStatusController($request, $response);
            $controller->setCurl($curlPlayback);
            $controller->setGuid($args['guid']);
            return $controller->execute();
        });

        /**
         * Requests that a specific mapping is deleted from the cache
         */
        $app->delete('/cache/{guid}', function ($request, $response, $args) use ($curlPlayback, $routing) {
            $controller = $routing->getItemDeleteController($request, $response);
            $controller->setCurl($curlPlayback);
            $controller->setPlaybackCache('/remote/cache/playback');
            $controller->setGuid($args['guid']);
            return $controller->execute();
        });
    }

    /**
     * List the pages in the cache, given a page and a page size
     *
     * This can use GET "/__admin/mappings" from the WireMock playback instance
     */
    protected function executeList($request, $response, $args, $curl)
    {
        $controller = $this->getCacheListController($request, $response);
        $controller->setCurl($curl);
        $controller->setPage(isset($args['page']) ? $args['page'] : 1);
        $controller->setPageSize(isset($args['pagesize']) ? $args['pagesize'] : 10);

        return $controller->execute();
    }

    protected function getCountController($request, $response)
    {
        return new \Proximate\Controller\Count($request, $response);
    }

    protected function getCountUrlController($request, $response)
    {
        return new \Proximate\Controller\CountUrl($request, $response);
    }

    protected function getCacheListController($request, $response)
    {
        return new \Proximate\Controller\CacheList($request, $response);
    }

    protected function getCacheSaveController($request, $response)
    {
        return new \Proximate\Controller\CacheSave($request, $response);
    }

    protected function getItemStatusController($request, $response)
    {
        return new \Proximate\Controller\ItemStatus($request, $response);
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

    protected function getStatusController($request, $response)
    {
        return new \Proximate\Controller\Status($request, $response);
    }
}
