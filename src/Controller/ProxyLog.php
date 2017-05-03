<?php

/**
 * Controller to fetch proxy logs
 */

namespace Proximate\Controller;

use Proximate\Controller\Base;
use Proximate\Exception\Init as InitException;
use IcyApril\Tail\Tail;
use IcyApril\Tail\Config;

class ProxyLog extends Base
{
    protected $logPath;

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
     * Performs a simple tail from the proxy logs
     *
     * @return array
     */
    protected function getLog()
    {
        $config = $this->getTailConfig($this->getLogPath());
        $config->setLines(100);

        $tail = $this->getTailInstance($config);
        $lines = $tail->getTail();

        return explode("\n", trim($lines));
    }

    protected function getTailConfig($logPath)
    {
        return new Config($logPath);
    }

    protected function getTailInstance(Config $config)
    {
        return new Tail($config);
    }

    public function setLogPath($logPath)
    {
        $this->logPath = $logPath;
    }

    protected function getLogPath()
    {
        if (!$this->logPath)
        {
            throw new InitException(
                "The proxy log controller needs a log path"
            );
        }

        return $this->logPath;
    }
}
