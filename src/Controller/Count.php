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
            $innerResult = [
                'ok' => true,
                'count' => $this->fetchCount(),
            ];
        }
        // @todo Put more detail in when handling more specific exceptions
        catch (\Exception $e)
        {
            $innerResult = ['ok' => false, ];
        }

        return $this->getResponse()->withJson(['result' => $innerResult, ]);
    }

    protected function fetchCount()
    {
        $mappings = $this->getCurl()->get('__admin/mappings');

        return isset($mappings['meta']['total']) ? $mappings['meta']['total'] : null;
    }
}
