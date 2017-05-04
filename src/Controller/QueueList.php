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
    protected $queuePath;

    /**
     * Main entry point
     *
     * @todo This is so similar to ItemDelete::execute() and probably others, can I refactor?
     *
     * @return \Slim\Http\Response
     */
    public function execute()
    {
        $status = $this->getStatus();
        try
        {
            $result = [
                'result' => [
                    'ok' => true,
                    'status' => $status,
                    'queue' => $this->fetchQueueList($status),
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

    public function setQueuePath($queuePath)
    {
        $this->queuePath = $queuePath;
    }

    protected function getQueuePath()
    {
        if (!$this->queuePath)
        {
            return new AppException("Queue path not set");
        }

        return $this->queuePath;
    }

    /**
     * @return array
     */
    protected function fetchQueueList($status)
    {
        // Get the required queue data matching the specified status
        $paths = $this->readQueueItemPaths($status);
        $list = $this->readQueueItems($paths);

        return $list;
    }

    /**
     * Reads all queue items of the specified status
     *
     * Note! The status must not come from user input, since it is not filtered here.
     *
     * @param string $status
     * @return array
     */
    protected function readQueueItemPaths($status)
    {
        $searchPath = $this->getQueuePath() . '/*.' . $status;
        $files = $this->getFileService()->glob($searchPath);

        return $files;
    }

    /**
     * For a list of JSON filenames, reads them all and concats them into a large array
     *
     * @todo Add the basename as an 'id' entry
     *
     * @param array $queueItemPaths
     * @return array
     */
    protected function readQueueItems(array $queueItemPaths)
    {
        $list = [];
        foreach ($queueItemPaths as $queueItemPath)
        {
            $json = file_get_contents($queueItemPath);
            $decoded = json_decode($json, true);
            $list[] = $decoded;
        }

        return $list;
    }
}