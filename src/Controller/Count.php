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
        try
        {
            $result = [
                'result' => [
                    'ok' => true,
                    'count' => $this->fetchCount(),
                ]
            ];
        }
        catch (\Exception $e)
        {
            $result = $this->getErrorResponse($e);
        }

        return $this->getResponse()->withJson($result);
    }

    protected function fetchCount()
    {
        $mappings = $this->getCurl()->get('__admin/mappings');

        return isset($mappings['meta']['total']) ? $mappings['meta']['total'] : null;
    }
}
