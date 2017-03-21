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
     * @todo The mappings endpoint does not presently support pagination, so we have to
     * get the whole lot and do the pagination ourselves. If this gets very large we'll have
     * to read it as a stream and capture the section we want, so as to avoid memory issues.
     *
     * @return array
     */
    protected function fetchList()
    {
        // @todo This needs parsing a bit to get just the important info
        $requests = $this->getCurl()->get('__admin/mappings');

        $mappings = isset($requests['mappings']) ? $requests['mappings'] : [];

        // Hmm, I think slice() might do all this stuff for us...
        $start = ($this->page - 1) * $this->pageSize;
        if ($start < count($mappings)) {
            $section = array_slice($mappings, $start, $this->pageSize);
        } else {
            $section = [];
        }

        return $section;
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
