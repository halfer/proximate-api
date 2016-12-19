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
        $rawParams = $this->getDecodedJsonBody();
        $validatedParams = $this->validateRequestParams($rawParams);

        $result = [
            'result' => [
                'ok' => $this->doQueue($validatedParams),
            ]
        ];

        return $this->getResponse()->withJson($result);
    }

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
     * @return boolean
     */
    protected function doQueue(array $fetchRequest)
    {
        return $this->
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
