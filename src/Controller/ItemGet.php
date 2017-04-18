<?php

/**
 * Controller to fetch a specific endpoint from the cache
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class ItemGet extends Base
{
    protected $guid;

    /**
     * Main entry point
     *
     * @todo This is so similar to ItemDelete::execute() and probably others, can I refactor?
     *
     * @return \Slim\Http\Response
     */
    public function execute()
    {
        try
        {
            $result = [
                'result' => [
                    'ok' => true,
                    'item' => $this->fetchItem(),
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

    public function setGuid($guid)
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * Calls the get cache function for the currently set mapping ID
     *
     * @tood Use a specific exception
     */
    protected function fetchItem()
    {
        if (!$this->guid)
        {
            throw new \Exception("No GUID set");
        }

        $cacheItem = $this->getCacheAdapter()->readCacheItem($this->guid);

        return $cacheItem;
    }
}
