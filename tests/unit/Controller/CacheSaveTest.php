<?php

/**
 * Unit tests for the cache entry creation controller
 */

namespace Proximate\Test;

use Proximate\Controller\CacheSave;
use Slim\Http\Request;
use Slim\Http\Response;
use Proximate\Queue\Write;

class CacheSaveTest extends \PHPUnit_Framework_TestCase
{
    // @todo Move to a parent class
    protected $request;
    protected $response;
    protected $queue;

    // @todo Fix up this skeleton/rough test implementation
    public function testCacheSaveWithJustUrl()
    {
        $this->
            getMockedRequest()->
            shouldReceive('getBody')->
            andReturn(
                json_encode(
                    ['url' => 'http://example.com', ]
                )
            );

        // @todo Check that the url, url_regex and reject_files get the right values
        $queue = $this->getMockedQueue();
        $queue->
            shouldReceive('setUrl')->
            andReturn($queue)->
            shouldReceive('setUrlRegex')->
            andReturn($queue)->
            shouldReceive('setRejectFiles')->
            andReturn($queue)->
            shouldReceive('queue');

        $this->
            getMockedResponse()->
            shouldReceive('withJson');

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testCacheSaveWithAllParameters()
    {
        $this->markTestIncomplete();
        // @todo Finish this
    }

    public function testBadCacheSaveParameters()
    {
        $this->markTestIncomplete();
        // @todo Finish this
    }

    public function testCacheSaveFailed()
    {
        $this->markTestIncomplete();
        // @todo Finish this
    }

    protected function getCacheSaveController()
    {
        $controller = new CacheSave($this->getMockedRequest(), $this->getMockedResponse());
        $controller->setQueue($this->getMockedQueue());

        return $controller;
    }

    /**
     * Gets the current request instance
     *
     * @return Request|\Mockery\Mock
     */
    protected function getMockedRequest()
    {
        return $this->request;
    }

    /**
     * Gets the current response instance
     *
     * @return Response|\Mockery\Mock
     */
    protected function getMockedResponse()
    {
        return $this->response;
    }

    /**
     * Gets the current queue instance
     *
     * @return Write|\Mockery\Mock
     */
    protected function getMockedQueue()
    {
        return $this->queue;
    }

    // @todo Move to a parent class
    public function setUp()
    {
        $this->request = \Mockery::mock(Request::class);
        $this->response = \Mockery::mock(Response::class);
        $this->queue = \Mockery::mock(Write::class);
    }

    // @todo Move to a parent class
    public function tearDown()
    {
        \Mockery::close();
    }
}
