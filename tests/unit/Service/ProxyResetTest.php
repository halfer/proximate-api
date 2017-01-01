<?php

/**
 * Unit tests for the proxy reset endpoint
 */

namespace Proximate\Test;

use Proximate\Service\ProxyReset as ProxyResetService;

class ProxyResetTest extends \PHPUnit_Framework_TestCase
{
    use CurlTrait;

    const DUMMY_RECORD_URL = 'http://example.com/hello';

    public function testSuccessfulResetCall()
    {
        $this->
            getMockedCurl()->
            shouldReceive('post')->
            with(
                '/start',
                ['url' => self::DUMMY_RECORD_URL, ]
            );
        $this->
            getProxyResetService()->
            execute(self::DUMMY_RECORD_URL);
    }

    /**
     * Ensures the world blows up if we don't supply a URL
     *
     * @expectedException \Proximate\Exception\RequiredParam
     */
    public function testMissingUrlResetCall()
    {
        $this->
            getMockedCurl()->
            shouldReceive('post');
        $this->
            getProxyResetService()->
            execute('');
    }

    /**
     * Ensure the world blows up if there is a cURL internal error
     *
     * @expectedException \Pest_Exception
     */
    public function testCurlErrorResetCall()
    {
        $this->
            getMockedCurl()->
            shouldReceive('post')->
            andThrow(new \Pest_Exception('Bork bork!'));
        $this->
            getProxyResetService()->
            execute(self::DUMMY_RECORD_URL);
    }

    protected function getProxyResetService()
    {
        $mock = \Mockery::mock(ProxyResetService::class)->
            makePartial();
        $mock->
            shouldAllowMockingProtectedMethods()->
            shouldReceive('sleep');
        $mock->init($this->getMockedCurl());

        return $mock;
    }
}
