<?php

/**
 * Controller to fetch proxy logs
 */

namespace Proximate\Controller;
use Proximate\Controller\Base;

class ProxyLog extends Base
{
    public function execute()
    {
        try
        {
            $result = [
                'result' => [
                    'ok' => true,
                    'log' => $this->getLog(),
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
     * FIXME needs to tail from the proxy logs
     *
     * @return array
     */
    protected function getLog()
    {
        return [
            'one',
            'two',
            'three',
        ];
    }
}
