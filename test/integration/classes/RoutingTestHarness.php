<?php

/**
 * Test harness for routing, instantiates fake controllers
 *
 * @todo Replace the fake controllers with controllers that extend the original and
 * override the execute() method, These do not check that the setters work correctly.
 */

namespace Proximate\Test;

use Proximate\Routing\Routing;
use Proximate\Controller\Base as BaseController;
use Proximate\Storage\BaseAdapter;

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

    protected function getItemGetController($request, $response)
    {
        return (new FakeController($request, $response))->setAction(__FUNCTION__);
    }

    protected function getItemDeleteController($request, $response)
    {
        return (new FakeController($request, $response))->setAction(__FUNCTION__);
    }

    protected function getProxyLogController($request, $response)
    {
        return (new FakeController($request, $response))->setAction(__FUNCTION__);
    }

    protected function getQueueListController($request, $response)
    {
        return (new FakeController($request, $response))->setAction(__FUNCTION__);
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

    public function setGuid($guid)
    {
        $this->data['guid'] = $guid;
    }

    public function setCacheAdapter(BaseAdapter $cacheAdapter)
    {
        $this->data['cache_adapter'] = get_class($cacheAdapter);
    }

    public function setLogPath($logPath)
    {
        $this->data['log_path'] = $logPath;
    }

    public function setStatus($status)
    {
        $this->data['status'] = $status;
    }

    public function setQueuePath($queuePath)
    {
        $this->data['queue_path'] = $queuePath;
    }

    public function setFileService($fileService)
    {
        $this->data['file_service'] = get_class($fileService);
    }
}
