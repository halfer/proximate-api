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
        return (new FakeController($request, $response))->setAction(__FUNCTION__);
    }

    protected function getCountUrlController($request, $response)
    {
        return (new FakeController($request, $response))->setAction(__FUNCTION__);
    }

    protected function getCacheListController($request, $response)
    {
        return (new FakeController($request, $response))->setAction(__FUNCTION__);
    }

    protected function getCacheSaveController($request, $response)
    {
        return (new FakeController($request, $response))->setAction(__FUNCTION__);
    }

    protected function getItemStatusController($request, $response)
    {
        return (new FakeController($request, $response))->setAction(__FUNCTION__);
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

    public function setAction($action)
    {
        $this->data['action'] = $action;

        return $this;
    }

    public function setPage($page)
    {
        $this->data['page'] = $page;
    }

    public function setPageSize($pageSize)
    {
        $this->data['pagesize'] = $pageSize;
    }
}
