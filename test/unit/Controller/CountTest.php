<?php

/**
 * Unit tests for the cache count controller
 */

namespace Proximate\Test;

use Proximate\Controller\Count as CountController;

class CountTest extends ControllerTestBase
{
    protected $cacheAdapter;

    public function testGoodCountCase()
    {
        $expectedCount = 5;
        $this->
            getCacheAdapterMock()->
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
        $this->
            getCacheAdapterMock()->
            shouldReceive('countCacheItems')->
            andThrow($this->getGeneralException());
        $this->setJsonResponseExpectation("An error occured");

        $this->
            getCountController()->
            execute();
    }

    protected function getCountController()
    {
        $controller = new CountController(
            $this->getMockedRequest(),
            $this->getMockedResponse()
        );
        $controller->setCacheAdapter($this->getCacheAdapterMock());

        return $controller;
    }
}
