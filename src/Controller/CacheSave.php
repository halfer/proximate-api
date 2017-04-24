<?php

/**
 * Controller to save a single site to the cache
 */

namespace Proximate\Controller;

use Proximate\Controller\Base;
use Proximate\Queue\Write as QueueWrite;
use Proximate\Exception\RequiredParam;
use Proximate\Exception\RequiredDependency;
use Proximate\Exception\UnexpectedParam;

class CacheSave extends Base
{
    protected $queue;

    public function execute()
    {
        // Any of these steps can result in an exception
        try
        {
            $rawParams = $this->getDecodedJsonBody();
            $validatedParams = $this->validateRequestParams($rawParams);
            $this->doQueue($validatedParams);

            // Everything was OK
            $result = ['result' => ['ok' => true, ]];
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
     * Checks the input parameters are OK, throws exceptions if not
     *
     * @param array $params
     * @return array
     * @throws RequiredParam
     * @throws UnexpectedParam
     */
    protected function validateRequestParams(array $params)
    {
        $validatedParams = [];

        // Ensure that url is present
        if (isset($params['url']))
        {
            $validatedParams['url'] = $params['url'];
        }
        else
        {
            throw new RequiredParam(
                "URL not present in request body"
            );
        }

        // Treatment for optional parameters
        $validatedParams['path_regex'] = isset($params['path_regex']) ?
            (string) $params['path_regex'] :
            null;

        // Ensure that no other items are permitted
        if ($this->hasNonPermittedKeys($params))
        {
            throw new UnexpectedParam(
                sprintf(
                    "The only permitted keys are: %s",
                    implode(', ', $this->getPermittedKeys())
                )
            );
        }

        return $validatedParams;
    }

    protected function hasNonPermittedKeys(array $params)
    {
        $ok = true;

        $permittedKeys = $this->getPermittedKeys();
        foreach (array_keys($params) as $key)
        {
            if (!in_array($key, $permittedKeys))
            {
                $ok = false;
                break;
            }
        }

        return !$ok;
    }

    // This is well out of date, what does the SimpleCrawler actually need?
    protected function getPermittedKeys()
    {
        return ['url', 'path_regex', ];
    }

    /**
     * Asks the queue service to create a queue item
     *
     * @param array $fetchRequest
     * @throws \Exception
     */
    protected function doQueue(array $fetchRequest)
    {
        $this->
            getQueue()->
            setUrl($fetchRequest['url'])->
            setPathRegex($fetchRequest['path_regex'])->
            queue();
    }

    public function setQueue(QueueWrite $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Gets the current queue object
     *
     * @return QueueWrite
     * @throws RequiredDependency
     */
    protected function getQueue()
    {
        if (!$this->queue)
        {
            throw new RequiredDependency(
                "This controller needs a queue object"
            );
        }

        return $this->queue;
    }
}
