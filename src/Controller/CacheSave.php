<?php

/**
 * Controller to save a single site to the cache
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;
use Proximate\Queue\Write as QueueWrite;

class CacheSave extends Base
{
    protected $queue;

    public function execute()
    {
        $result = ['result' => []];

        // Any of these steps can result in an exception
        try
        {
            $rawParams = $this->getDecodedJsonBody();
            $validatedParams = $this->validateRequestParams($rawParams);
            $this->doQueue($validatedParams);

            // Everything was OK
            $result['result']['ok'] = true;
        }
        // @todo Treat non-specific exceptions more cautiously
        catch (\Exception $e)
        {
            // We got a failure
            $result['result']['ok'] = false;
            $result['result']['error'] = $e->getMessage();
        }

        return $this->getResponse()->withJson($result);
    }

    // @todo Use specific exceptions, so unexpected exceptions can be
    // treated more cautiously
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
            throw new \Exception(
                "URL not present in request body"
            );
        }

        // Treatment for optional parameters
        $validatedParams['url_regex'] = isset($params['url_regex']) ?
            (string) $params['url_regex'] :
            null;
        $validatedParams['reject_files'] = isset($params['reject_files']) ?
            (string) $params['reject_files'] :
            null;

        // Ensure that no other items are permitted
        if ($this->hasNonPermittedKeys($params))
        {
            throw new \Exception(
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

    protected function getPermittedKeys()
    {
        return ['url', 'url_regex', 'reject_files', ];
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
            setUrlRegex($fetchRequest['url_regex'])->
            setRejectFiles($fetchRequest['reject_files'])->
            queue();
    }

    public function setQueue(QueueWrite $queue)
    {
        $this->queue = $queue;
    }

    // @todo Use specific exceptions, so unexpected exceptions can be
    // treated more cautiously
    protected function getQueue()
    {
        if (!$this->queue)
        {
            throw new \Exception(
                "This controller needs a queue object"
            );
        }

        return $this->queue;
    }
}
