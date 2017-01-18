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
            getCurlMock()->
            shouldReceive('post')->
            with('/start?url=' . urlencode(self::DUMMY_RECORD_URL), []);
        $this->
            getProxyResetService()->
            resetRecorder(self::DUMMY_RECORD_URL);
    }

    /**
     * Ensures the world blows up if we don't supply a URL
     *
     * @expectedException \Proximate\Exception\RequiredParam
     */
    public function testMissingUrlResetCall()
    {
        $this->
            getCurlMock()->
            shouldReceive('post');
        $this->
            getProxyResetService()->
            resetRecorder('');
    }

    /**
     * Ensure the world blows up if there is a cURL internal error
     *
     * @expectedException \Pest_Exception
     */
    public function testCurlErrorResetCall()
    {
        $this->
            getCurlMock()->
            shouldReceive('post')->
            andThrow(new \Pest_Exception('Bork bork!'));
        $this->
            getProxyResetService()->
            resetRecorder(self::DUMMY_RECORD_URL);
    }

    protected function getProxyResetService()
    {
        $mock = \Mockery::mock(ProxyResetService::class)->
            makePartial();
        $mock->
            shouldAllowMockingProtectedMethods()->
            shouldReceive('sleep');
        $mock->init($this->getCurlMock());

        return $mock;
    }
}
