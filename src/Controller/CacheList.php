<?php

/**
 * Controller to fetch pages of cache items from the proxy
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class CacheList extends Base
{
    protected $page;
    protected $pageSize;

    public function execute()
    {
        try
        {
            $result = [
                'result' => [
                    'ok' => true,
                    'list' => $this->fetchList(),
                ]
            ];
            $statusCode = 200;
        }
        catch (\Exception $e)
        {
            $result = $this->getErrorResponse($e);
            $statusCode = 500;
        }

        return $this->createJsonResponse($result, $statusCode);
    }

    protected function fetchList()
    {
        // @todo This needs parsing a bit to get just the important info
        $requests = $this->getCurl()->get('__admin/requests');

        return $requests;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }
}
