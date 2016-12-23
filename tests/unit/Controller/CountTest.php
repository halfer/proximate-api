<?php

/**
 * Unit tests for the cache count controller
 */

namespace Proximate\Test;

use Proximate\Controller\Count as CountController;

class CountTest extends ControllerTestBase
{
    protected $curl;

    public function testGoodCountCase()
    {
        $expectedResult = [
            'meta' => [
                'total' => $expectedCount = 5,
            ]
        ];
        $this->getMockedCurl()->
            shouldReceive('get')->
            with('__admin/mappings')->
            andReturn($expectedResult);

        $this->setJsonResponseExpectation(null, ['count' => $expectedCount, ]);

        $this->
            getCountController()->
            execute();
    }

    /**
     * Checks that a general error in the curl module is reported cautiously
     *
     * (There is no app-level error that can come from the cURL module, since it is third-party)
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
        $this->getMockedCurl()->
            shouldReceive('get')->
            andThrow($exception);
        $this->setJsonResponseExpectation($expectedError);

        $this->
            getCountController()->
            execute();
    }

    protected function getCountController()
    {
        $controller = new CountController($this->getMockedRequest(), $this->getMockedResponse());
        $controller->setCurl($this->getMockedCurl());

        return $controller;
    }

    /**
     * Gets a mock of the cURL instance
     *
     * @return \PestJSON|Mockery\Mock
     */
    protected function getMockedCurl()
    {
        return $this->curl;
    }

    public function setUp()
    {
        parent::setUp();
        $this->curl = \Mockery::mock(\PestJSON::class);
    }
}
