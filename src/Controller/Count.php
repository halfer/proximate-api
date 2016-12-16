<?php

/**
 * Controller for the count function
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class Count extends Base
{
    public function execute()
    {
        $result = [
            'result' => [
                'count' => $this->fetchCount(),
            ]
        ];

        return $this->getResponse()->withJson($result);
    }

    protected function fetchCount()
    {
        $mappings = $this->getCurl()->get('__admin/mappings');

        return isset($mappings['meta']['total']) ? $mappings['meta']['total'] : null;
    }
}
