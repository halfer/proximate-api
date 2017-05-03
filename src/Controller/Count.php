<?php

/**
 * Controller for the count function
 */

namespace Proximate\Controller;

use Proximate\Controller\Base;

class Count extends Base
{
    /**
     * Main controller entry point
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
                    'count' => $this->fetchCount(),
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

    protected function fetchCount()
    {
        return $this->getCacheAdapter()->countCacheItems();
    }
}
