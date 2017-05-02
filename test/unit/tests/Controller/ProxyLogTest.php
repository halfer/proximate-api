<?php

/**
 * Unit tests for the proxy log controller
 */

namespace Proximate\Test;

use Proximate\Controller\ProxyLog;

class ProxyLogTest extends ControllerTestBase
{
    const LOG_PATH = '/here/is/a/path';

    public function testGoodLogCase()
    {
        $controller = $this->getController();
        $controller->setLogPath(self::LOG_PATH);
        $this->setupTailMocks(
            $controller,
            $demoLog = "Log 1\nLog 2\nLog 3\n"
        );
        $this->setJsonResponseExpectation(
            null,
            ['log' => ['Log 1', 'Log 2', 'Log 3', ]]
        );

        $controller->execute();
    }

    protected function setupTailMocks(ProxyLog $controller, $demoLog)
    {
        $config = \Mockery::mock(\IcyApril\Tail\Config::class);
        $config->
            shouldReceive('setLines')->
            with(100);

        $tail = \Mockery::mock(\IcyApril\Tail\Tail::class);
        $tail->
            shouldReceive('getTail')->
            andReturn($demoLog);

        $controller->
            shouldReceive('getTailConfig')->
            with(self::LOG_PATH)->
            andReturn($config)->
            shouldReceive('getTailInstance')->
            with($config)->
            andReturn($tail)
        ;
    }

    public function testCurlFailsLogCase()
    {
        $this->markTestIncomplete();
    }

    protected function getController()
    {
        $controller = \Mockery::mock(ProxyLog::class)->
            makePartial()->
            shouldAllowMockingProtectedMethods();
        /* @var $controller \Mockery\Mock|\Proximate\Controller\ProxyLog */
        $controller->setRequest($this->getMockedRequest());
        $controller->setResponse($this->getMockedResponse());

        return $controller;
    }
}
