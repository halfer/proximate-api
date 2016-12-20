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
        $url = 'http://example.com';
        $this->setBodyExpectation(['url' => $url, ]);
        $this->setQueueExpectation($url, null, null);

        $this->
            getMockedResponse()->
            shouldReceive('withJson')->
            with(['result' => ['ok' => true, ]]);

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

    protected function setBodyExpectation(array $params)
    {
        $this->
            getMockedRequest()->
            shouldReceive('getBody')->
            andReturn(
                json_encode($params)
            );
    }

    protected function setQueueExpectation($url, $urlRegex, $rejectFiles)
    {
        $queue = $this->getMockedQueue();
        $queue->
            shouldReceive('setUrl')->
            with($url)->
            andReturn($queue)->
            shouldReceive('setUrlRegex')->
            with($urlRegex)->
            andReturn($queue)->
            shouldReceive('setRejectFiles')->
            with($rejectFiles)->
            andReturn($queue)->
            shouldReceive('queue')->
            andReturn(true);
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
