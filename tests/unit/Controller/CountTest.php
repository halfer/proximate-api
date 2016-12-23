<?php

/**
 * Unit tests for the cache count controller
 */

namespace Proximate\Test;

use Proximate\Controller\Count as CountController;

class CountTest extends ControllerTestBase
{
    public function testGoodCountCase()
    {
        $expectedResult = [
            'meta' => [
                'total' => $expectedCount = 5,
            ]
        ];
        $curl = $this->getMockedCurl();
        $curl->
            shouldReceive('get')->
            with('__admin/mappings')->
            andReturn($expectedResult);

        $this->setJsonResponseExpectation(null, ['count' => $expectedCount, ]);

        $controller = $this->getCountController();
        $controller->setCurl($curl);
        $controller->execute();
    }

    public function testCurlFailsCountCase()
    {
        $this->markTestIncomplete();
    }

    protected function getCountController()
    {
        $controller = new CountController($this->getMockedRequest(), $this->getMockedResponse());

        return $controller;
    }

    /**
     * Gets a mock of the cURL instance
     *
     * @return \PestJSON|Mockery\Mock
     */
    protected function getMockedCurl()
    {
        return \Mockery::mock(\PestJSON::class);
    }
}
