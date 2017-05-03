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

    /**
     * Fetches the list of cached URLs
     *
     * @return array
     */
    protected function fetchList()
    {
        return $this->getCacheAdapter()->getPageOfCacheItems($this->page, $this->pageSize);
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
