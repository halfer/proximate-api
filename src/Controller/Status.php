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
                    'file_logs' => $this->getFileOperationLogs(),
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

    /**
     * Could use tail() or similar to just get the last logs here
     *
     * @return string
     */
    protected function getFileOperationLogs()
    {
        return file_exists('/tmp/proxy-copy.log') ?
            file_get_contents('/tmp/proxy-copy.log') :
            null;
    }
}
