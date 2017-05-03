<?php

/**
 * Controller to fetch lists of queue items by status
 */

namespace Proximate\Controller;

use Proximate\Controller\Base;
use Proximate\Exception\App as AppException;

class QueueList extends Base
{
    protected $status;

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
                    'queue' => $this->fetchQueueList(),
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

    public function setStatus($status)
    {
        $this->status = $status;
    }

    protected function getStatus()
    {
        if (!$this->status)
        {
            return new AppException("Status not set");
        }

        return $this->status;
    }

    /**
     * @return array
     */
    protected function fetchQueueList()
    {
        $status = $this->getStatus();

        return [
            'name' => 1,
            'status' => 'rah',
        ];
    }
}