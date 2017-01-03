<?php

/**
 * Test harness for routing, instantiates fake controllers
 */

namespace Proximate\Test;

use Proximate\Routing\Routing;
use Proximate\Controller\Base as BaseController;

class RoutingTestHarness extends Routing
{
    protected function getCountController($request, $response)
    {
        return new FakeController($request, $response);
    }

    protected function getCountUrlController($request, $response)
    {
        return new FakeController($request, $response);
    }

    protected function getCacheListController($request, $response)
    {
        return new FakeController($request, $response);
    }

    protected function getCacheSaveController($request, $response)
    {
        return new FakeController($request, $response);
    }

    protected function getItemStatusController($request, $response)
    {
        return new FakeController($request, $response);
    }

    protected function getItemDeleteController($request, $response)
    {
        // FIXME
    }
}

class FakeController extends BaseController
{
    protected $data = [];

    public function execute()
    {
        $result = $this->data;
        $statusCode = 200;

        return $this->createJsonResponse($result, $statusCode);
    }

    public function setUrl($url)
    {
        $this->data['url'] = $url;
    }

    public function setQueue($queue)
    {
        $this->data['queue'] = get_class($queue);
    }
}