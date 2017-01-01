<?php

/**
 * Adds curl features to a test class
 *
 * Offered as a trait since it can be injected into any sort of class
 */

namespace Proximate\Test;

trait CurlTrait
{
    protected $curl;

    /**
     * Gets the current curl instance
     *
     * @return \PestJSON|\Mockery\Mock
     */
    protected function getCurlMock()
    {
        return $this->curl;
    }

    public function setUp()
    {
        parent::setUp();
        $this->curl = \Mockery::mock(\PestJSON::class);
    }
}
