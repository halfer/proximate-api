<?php

/**
 * Unit tests for the URL-specific cache count controller
 */

namespace Proximate\Test;

use Proximate\Controller\CountUrl as CountUrlController;

class CountUrlTest extends ControllerTestBase
{
    use CurlTrait;

    public function testGoodCountUrlCase()
    {
        $this->markTestIncomplete();
    }

    public function testCurlCountUrlGeneralFailure()
    {
        $this->markTestIncomplete();
    }

    protected function getCountController()
    {
        $controller = new CountUrlController($this->getMockedRequest(), $this->getMockedResponse());
        $controller->setCurl($this->getCurlMock());

        return $controller;
    }
}
