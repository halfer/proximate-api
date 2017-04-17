<?php

/**
 * Unit tests for the cache item delete controller
 */

namespace Proximate\Test;

use Proximate\Controller\ItemDelete as ItemDeleteController;

class DeleteTest extends ControllerTestBase
{
    public function testGoodDeleteCase()
    {
        $guid = '282790cd-a154-31fc-8e41-60ad3a0d154a';
        $this->
            getCacheAdapterMock()->
            shouldReceive('expireCacheItem')->
            with($guid);

        $this->setJsonResponseExpectation(null, []);

        $this->
            getDeleteController()->
            setGuid($guid)->
            execute();
    }

    public function testDeleteFailure()
    {
        $this->
            getCacheAdapterMock()->
            shouldReceive('countCacheItems')->
            andThrow($this->getGeneralException());
        $this->setJsonResponseExpectation("An error occured");

        $this->
            getDeleteController()->
            execute();
    }

    protected function getDeleteController()
    {
        $controller = new ItemDeleteController(
            $this->getMockedRequest(),
            $this->getMockedResponse()
        );
        $controller->setCacheAdapter($this->getCacheAdapterMock());

        return $controller;
    }
}
