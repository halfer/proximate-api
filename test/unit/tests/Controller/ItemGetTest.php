<?php

/**
 * Unit tests for the cache item fetch controller
 */

namespace Proximate\Test;

use Cache\Adapter\Common\CacheItem;
use Proximate\Controller\ItemGet as ItemGetController;

class ItemGetTest extends ControllerTestBase
{
    const GUID = '282790cd-a154-31fc-8e41-60ad3a0d154a';

    public function testGoodFetchCase()
    {
        $response = "Header: one\r\nHeader2: two\r\n\r\nBody\r\n";

        $this->
            getCacheAdapterMock()->
            shouldReceive('readCacheItem')->
            with(self::GUID)->
            andReturn($response);

        $this->setJsonResponseExpectation(null, ['item' => $response, ]);

        $this->
            getFetchController()->
            setGuid(self::GUID)->
            execute();

    }

    public function testFetchFailure()
    {
        $this->
            getCacheAdapterMock()->
            shouldReceive('getItem')->
            andThrow($this->getGeneralException());
        $this->setJsonResponseExpectation("An error occured");

        $this->
            getFetchController()->
            setGuid(self::GUID)->
            execute();
    }

    protected function getFetchController()
    {
        $controller = new ItemGetController(
            $this->getMockedRequest(),
            $this->getMockedResponse()
        );
        $controller->setCacheAdapter($this->getCacheAdapterMock());

        return $controller;
    }
}