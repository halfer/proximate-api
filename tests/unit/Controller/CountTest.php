<?php

/**
 * Unit tests for the cache count controller
 */

namespace Proximate\Test;

use Proximate\Controller\Count as CountController;
use Proximate\CacheAdapter\Filesystem;

class CountTest extends ControllerTestBase
{
    protected $cacheAdapter;

    public function testGoodCountCase()
    {
        $expectedCount = 5;
        $this->getCacheAdapterMock()->
            shouldReceive('countCacheItems')->
            andReturn($expectedCount);

        $this->setJsonResponseExpectation(null, ['count' => $expectedCount, ]);

        $this->
            getCountController()->
            execute();
    }

    /**
     * Checks that a general error in the cache module is reported cautiously
     */
    public function testCurlCountGeneralFailure()
    {
        $this->checkCacheSaveFailure(
            "An error occured",
            $this->getGeneralException()
        );
    }

    protected function checkCacheSaveFailure($expectedError, \Exception $exception)
    {
        $this->
            getCacheAdapterMock()->
            shouldReceive('countCacheItems')->
            andThrow($exception);
        $this->setJsonResponseExpectation($expectedError);

        $this->
            getCountController()->
            execute();
    }

    protected function getCountController()
    {
        $controller = new CountController($this->getMockedRequest(), $this->getMockedResponse());
        $controller->setCacheAdapter($this->getCacheAdapterMock());

        return $controller;
    }

    public function setUp()
    {
        $this->cacheAdapter = \Mockery::mock(Filesystem::class);
        parent::setUp();
    }

    protected function getCacheAdapterMock()
    {
        return $this->cacheAdapter;
    }
}
