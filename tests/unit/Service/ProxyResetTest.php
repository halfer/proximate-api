<?php

/**
 * Unit tests for the proxy reset endpoint
 */

namespace Proximate\Test;

use Proximate\Service\ProxyReset as ProxyResetService;

class ProxyResetTest extends \PHPUnit_Framework_TestCase
{
    use CurlTrait {
        setUp as traitSetUp;
    }

    const DUMMY_RECORD_URL = 'http://example.com/hello';

    protected $curlProxy;

    /**
     * Adds another curl mock to the test class
     *
     * CurlTrait brings in $this->curl, which we will use for the API, so will add
     * another one here for the proxy.
     */
    public function setUp()
    {
        parent::setUp();
        $this->traitSetUp();
        $this->curlProxy = \Mockery::mock(\PestJSON::class);
    }

    public function testSuccessfulResetCall()
    {
        $this->
            getCurlApiMock()->
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
            getCurlApiMock()->
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
            getCurlApiMock()->
            shouldReceive('post')->
            andThrow(new \Pest_Exception('Bork bork!'));
        $this->
            getProxyResetService()->
            resetRecorder(self::DUMMY_RECORD_URL);
    }

    /**
     * @todo Rename above tests to specify they are for the recorder restart
     * @todo Write some new test(s) to test the general restart method
     */
    public function testWiremockRestart() {
        $this->markTestIncomplete();
    }

    protected function getProxyResetService()
    {
        $mock = \Mockery::mock(ProxyResetService::class)->
            makePartial();
        $mock->
            shouldAllowMockingProtectedMethods()->
            shouldReceive('sleep');
        $mock->init($this->getCurlApiMock(), $this->getCurlProxyMock());

        return $mock;
    }

    protected function getCurlApiMock()
    {
        return $this->getCurlMock();
    }

    protected function getCurlProxyMock()
    {
        return $this->curlProxy;
    }
}
