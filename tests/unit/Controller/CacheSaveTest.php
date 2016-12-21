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
        $this->setBodyExpectation([
            'url' => $url = 'http://example.com',
        ]);
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
        $this->setBodyExpectation([
            'url' => $url = 'http://example.com',
            'url_regex' => $urlRegex = '/section/*.html',
            'reject_files' => $rejectFiles = '*.png',
        ]);
        $this->setQueueExpectation($url, $urlRegex, $rejectFiles);

        $this->
            getMockedResponse()->
            shouldReceive('withJson')->
            with(['result' => ['ok' => true, ]]);

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testMissingCacheSaveParameter()
    {
        $this->setBodyExpectation([
            'url_regex' => $urlRegex = '/section/*.html',
            'reject_files' => $rejectFiles = '*.png',
        ]);
        $this->setQueueExpectation(null, $urlRegex, $rejectFiles);

        $this->
            getMockedResponse()->
            shouldReceive('withJson')->
            with(['result' => [
                'ok' => false,
                'error' => 'URL not present in request body',
            ]]);

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testBadCacheSaveParameters()
    {
        $this->setBodyExpectation([
            'url' => $url = 'http://example.com',
            'unidentified_flying_object' => 1,
        ]);
        $this->setQueueExpectation($url, null, null);

        $this->
            getMockedResponse()->
            shouldReceive('withJson')->
            with(['result' => [
                'ok' => false,
                'error' => 'The only permitted keys are: url, url_regex, reject_files',
            ]]);

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testBadCacheSaveJson()
    {
        $this->setBadBodyExpectation();
        $this->setQueueExpectation('http://example.com', null, null);

        $this->
            getMockedResponse()->
            shouldReceive('withJson')->
            with(['result' => [
                'ok' => false,
                 'error' => 'The JSON body could not be decoded',
            ]]);

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testCacheSaveFailed()
    {
        $this->setBodyExpectation([
            'url' => $url = 'http://example.com',
        ]);
        $this->setQueueExpectation($url, null, null, false);

        $this->
            getMockedResponse()->
            shouldReceive('withJson')->
            with(['result' => [
                'ok' => false,
                'error' => 'Emulated error',
            ]]);

        $this->
            getCacheSaveController()->
            execute();
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

    protected function setBadBodyExpectation()
    {
        $this->
            getMockedRequest()->
            shouldReceive('getBody')->
            andReturn("Hello there");
    }

    protected function setQueueExpectation($url, $urlRegex, $rejectFiles, $returnOk = true)
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
            andReturn($queue);
        if ($returnOk)
        {
            $queue->
                shouldReceive('queue');
        }
        else
        {
            $queue->
                shouldReceive('queue')->
                andThrow(new \Exception("Emulated error"));
        }
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
