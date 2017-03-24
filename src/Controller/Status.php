<?php

/**
 * Controller to view the status of a system
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class Status extends Base
{
    public function execute()
    {
        try
        {
            $result = [
                'result' => [
                    'recorder' => ['sites' => $this->getWaitingSites(), ],
                    'ok' => true,
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
     *
     * @todo Don't hardwire path, use setRecordCache
     * @todo Use file service glob
     * @todo Write tests
     *
     * @return array
     */
    protected function getWaitingSites()
    {
        $path = '/remote/cache/record/';
        $files = glob($path . '*');

        // Ignore default proxy folders
        $flipped = array_flip($files);
        foreach (['mappings', '__files'] as $ignore)
        {
            if (isset($flipped[$path . $ignore]))
            {
                unset($flipped[$path . $ignore]);
            }
        }

        return array_flip($flipped);
    }
}