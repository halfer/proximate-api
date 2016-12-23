<?php

/**
 * Unit tests for the cache entry creation controller
 */

namespace Proximate\Test;

use Proximate\Controller\CacheSave;
use Proximate\Queue\Write;

class CacheSaveTest extends ControllerTestBase
{
    protected $queue;

    public function testCacheSaveWithJustUrl()
    {
        $this->setRequestBodyExpectation([
            'url' => $url = 'http://example.com',
        ]);
        $this->setQueueExpectation($url, null, null);

        $this->setJsonResponseExpectation();

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testCacheSaveWithAllParameters()
    {
        $this->setRequestBodyExpectation([
            'url' => $url = 'http://example.com',
            'url_regex' => $urlRegex = '/section/*.html',
            'reject_files' => $rejectFiles = '*.png',
        ]);
        $this->setQueueExpectation($url, $urlRegex, $rejectFiles);

        $this->setJsonResponseExpectation();

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testMissingCacheSaveParameter()
    {
        $this->setRequestBodyExpectation([
            'url_regex' => $urlRegex = '/section/*.html',
            'reject_files' => $rejectFiles = '*.png',
        ]);
        $this->setQueueExpectation(null, $urlRegex, $rejectFiles);

        $this->setJsonResponseExpectation('URL not present in request body');

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testBadCacheSaveParameters()
    {
        $this->setRequestBodyExpectation([
            'url' => $url = 'http://example.com',
            'unidentified_flying_object' => 1,
        ]);
        $this->setQueueExpectation($url, null, null);

        $this->setJsonResponseExpectation('The only permitted keys are: url, url_regex, reject_files');

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testBadCacheSaveJson()
    {
        $this->setBadBodyExpectation();
        $this->setQueueExpectation('http://example.com', null, null);

        $this->setJsonResponseExpectation('The JSON body could not be decoded');

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testCacheSaveFailed()
    {
        $this->setRequestBodyExpectation([
            'url' => $url = 'http://example.com',
        ]);
        $this->setQueueExpectation($url, null, null, false);

        $this->setJsonResponseExpectation('Emulated error');

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

    protected function setRequestBodyExpectation(array $params)
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

    protected function setJsonResponseExpectation($error = null)
    {
        $expectedJson = ['ok' => !$error, ];
        if ($error)
        {
            $expectedJson['error'] = $error;
        }
        $this->
            getMockedResponse()->
            shouldReceive('withJson')->
            with(['result' => $expectedJson, ]);
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

    public function setUp()
    {
        parent::setUp();
        $this->queue = \Mockery::mock(Write::class);
    }
}
