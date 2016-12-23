<?php

/**
 * Base for controller tests
 */

namespace Proximate\Test;

use Slim\Http\Request;
use Slim\Http\Response;

abstract class ControllerTestBase extends \PHPUnit_Framework_TestCase
{
    protected $request;
    protected $response;

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

    protected function setJsonResponseExpectation($error = null, array $additionalValues = [])
    {
        $expectedJson = ['ok' => !$error, ];
        if ($error)
        {
            $expectedJson['error'] = $error;
        }

        $outerJson = ['result' => array_merge($expectedJson, $additionalValues), ];
        $this->
            getMockedResponse()->
            shouldReceive('withJson')->
            with($outerJson, $expectedJson['ok'] ? 200 : 500);
    }

    protected function getGeneralException()
    {
        return new \Exception("Error that could contain sensitive info");
    }

    public function setUp()
    {
        $this->request = \Mockery::mock(Request::class);
        $this->response = \Mockery::mock(Response::class);
   }

    public function tearDown()
    {
        \Mockery::close();
    }
}
