<?php

/**
 * Simple controller parent class
 */

namespace Proximate\Controller;

abstract class Base
{
    protected $request;
    protected $response;

    public function __construct($request, $response)
    {
        $this->
            setRequest($request)->
            setResponse($response);
    }

    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    protected function getRequest()
    {
        return $this->request;
    }

    protected function getResponse()
    {
        return $this->response;
    }

    abstract public function execute();
}
