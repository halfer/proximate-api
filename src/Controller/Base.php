<?php

/**
 * Simple controller parent class
 */

namespace Proximate\Controller;

use Proximate\Exception\BadJson as BadJsonException;

abstract class Base
{
    protected $request;
    protected $response;
    protected $curl;

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

    /**
     * Returns the current request
     *
     * @return \Slim\Http\Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the request body
     *
     * @return string
     */
    protected function getBody()
    {
        return $this->getRequest()->getBody();
    }

    /**
     * Returns the request body as decoded JSON
     *
     * @return array
     */
    protected function getDecodedJsonBody()
    {
        $decoded = json_decode($this->getBody(), true);
        if ($decoded === null)
        {
            throw new BadJsonException(
                "The JSON body could not be decoded"
            );
        }

        return $decoded;
    }

    /**
     * Gets the current response object
     *
     * @return \Slim\Http\Response
     */
    protected function getResponse()
    {
        return $this->response;
    }

    public function setCurl(\PestJSON $curl)
    {
        $this->curl = $curl;

        return $this;
    }

    /**
     * Get current curl instance
     *
     * @return \PestJSON
     */
    public function getCurl()
    {
        return $this->curl;
    }

    abstract public function execute();

    protected function setJsonHeader()
    {
        #global $app;
        $newResponse = $this->getResponse()->withHeader('Content-type', 'application/json');
        #$app->setResponse($newResponse);
    }
}
