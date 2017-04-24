<?php

/**
 * Unit tests for the cache entry creation controller
 */

namespace Proximate\Test;

use Proximate\Controller\CacheSave as CacheSaveController;
use Proximate\Queue\Write;
use Proximate\Exception\AlreadyQueued as AlreadyQueuedException;

class CacheSaveTest extends ControllerTestBase
{
    const EXAMPLE_URL = 'http://example.com';

    protected $queue;

    public function testCacheSaveWithJustUrl()
    {
        $this->setRequestBodyExpectation([
            'url' => $url = self::EXAMPLE_URL,
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
            'url' => $url = self::EXAMPLE_URL,
            'path_regex' => $pathRegex = '/section/*.html',
        ]);
        $this->setQueueExpectation($url, $pathRegex);

        $this->setJsonResponseExpectation();

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testMissingCacheSaveParameter()
    {
        $this->setRequestBodyExpectation([
            'path_regex' => $urlRegex = '/section/*.html',
        ]);
        $this->setQueueExpectation(null, $urlRegex);

        $this->setJsonResponseExpectation('URL not present in request body');

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testBadCacheSaveParameters()
    {
        $this->setRequestBodyExpectation([
            'url' => $url = self::EXAMPLE_URL,
            'unidentified_flying_object' => 1,
        ]);
        $this->setQueueExpectation($url, null, null);

        $this->setJsonResponseExpectation('The only permitted keys are: url, path_regex');

        $this->
            getCacheSaveController()->
            execute();
    }

    public function testBadCacheSaveJson()
    {
        $this->setBadBodyExpectation();
        $this->setQueueExpectation(self::EXAMPLE_URL, null, null);

        $this->setJsonResponseExpectation('The JSON body could not be decoded');

        $this->
            getCacheSaveController()->
            execute();
    }

    /**
     * Ensures that a queue error of our own is reported fully
     */
    public function testCacheSaveAppFailure()
    {
        $this->checkCacheSaveFailure(
            $error = "Emulated error",
            new AlreadyQueuedException($error)
        );
    }

    /**
     * Ensures that a queue error from outside of our app is reported cautiously
     */
    public function testCacheSaveGeneralFailure()
    {
        $this->checkCacheSaveFailure(
            "An error occured",
            $this->getGeneralException()
        );
    }

    protected function checkCacheSaveFailure($expectedError, \Exception $exception)
    {
        $this->setRequestBodyExpectation([
            'url' => $url = self::EXAMPLE_URL,
        ]);
        $this->setQueueExpectation($url, null, null, $exception);

        $this->setJsonResponseExpectation($expectedError);

        $this->
            getCacheSaveController()->
            execute();
    }

    protected function getCacheSaveController()
    {
        $controller = new CacheSaveController($this->getMockedRequest(), $this->getMockedResponse());
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

    protected function setQueueExpectation($url, $pathRegex, \Exception $exception = null)
    {
        $queue = $this->getMockedQueue();
        $queue->
            shouldReceive('setUrl')->
            with($url)->
            andReturn($queue)->
            shouldReceive('setPathRegex')->
            with($pathRegex)->
            andReturn($queue);
        if (!$exception)
        {
            $queue->
                shouldReceive('queue');
        }
        else
        {
            $queue->
                shouldReceive('queue')->
                andThrow($exception);
        }
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
