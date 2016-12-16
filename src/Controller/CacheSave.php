<?php

/**
 * Controller to save a single site to the cache
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;
use Proximate\Queue;

class CacheSave extends Base
{
    public function execute()
    {
        $result = [
            'result' => [
                'ok' => $this->doQueue(),
            ]
        ];

        return $this->getResponse()->withJson($result);
    }

    protected function doQueue()
    {
        $queue = new Queue();
        $ok = $queue->
            setUrl('http://www.nimvelo.com/about/careers/')->
            setUrlRegex('.*(/about/careers/.*)|(/job/.*)')->
            queue();

        return $ok;
    }
}
