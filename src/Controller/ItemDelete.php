<?php

/** 
 * Controller to delete a specific endpoit from the cache
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class ItemDelete extends Base
{
    protected $guid;

    /**
     * Main entry point
     *
     * @return \Slim\Http\Response
     */
    public function execute()
    {
        try
        {
            $result = [
                'result' => [
                    'ok' => $this->deleteItem(),
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
    }

    /**
     * Calls the delete endpoint for the currently set mapping ID
     *
     * @tood Use a specific exception
     */
    protected function deleteItem()
    {
        if (!$this->guid)
        {
            throw new \Exception("No GUID set");
        }

        $this->getCacheAdapter()->expireCacheItem($this->guid);

        return true;
    }

    public function setPlaybackCache($playbackCache)
    {
        $this->playbackCache = $playbackCache;
    }
}
