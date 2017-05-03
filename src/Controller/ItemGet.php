<?php

/**
 * Controller to fetch a specific endpoint from the cache
 */

namespace Proximate\Controller;

use Proximate\Controller\Base;
use Proximate\Exception\App as AppException;
use Cache\Adapter\Common\Exception\InvalidArgumentException as CacheArgumentException;

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

        try
        {
            $cacheItem = $this->getCacheAdapter()->readCacheItem($this->guid);
        }
        // Cache param exceptions are made public, e.g.
        // Invalid key "a-b". Valid filenames must match [a-zA-Z0-9_\.! ]
        catch (CacheArgumentException $e)
        {
            throw new AppException($e->getMessage());
        }
        // Any other problems are just rethrown
        catch (\Exception $e)
        {
            throw $e;
        }

        return $cacheItem;
    }
}
