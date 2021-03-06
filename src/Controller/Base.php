<?php

/**
 * Simple controller parent class
 */

namespace Proximate\Controller;

use Proximate\Storage\Filesystem as CacheAdapter;
use Proximate\Storage\BaseAdapter;
use Proximate\Service\File as FileService;
use Proximate\Exception\BadJson as BadJsonException;
use Proximate\Exception\App as AppException;

abstract class Base
{
    protected $request;
    protected $response;
    protected $curl;
    protected $cacheAdapter;
    protected $fileService;

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

    protected function getErrorResponse(\Exception $exception)
    {
        // Treat non-specific exceptions more cautiously
        $message = $exception instanceof AppException ?
            $exception->getMessage() :
            "An error occured";

        return [
            'result' => [
                'ok' => false,
                'error' => $message,
            ]
        ];
    }

    protected function createJsonResponse($json, $statusCode)
    {
        return $this->
            getResponse()->
            withJson($json, $statusCode, JSON_PRETTY_PRINT);
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
        if (!$this->curl)
        {
            throw new AppException("No curl object set in controller");
        }

        return $this->curl;
    }

    public function setCacheAdapter(BaseAdapter $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
    }

    /**
     * Gets the cache adapter to query the cache
     *
     * @return CacheAdapter
     * @throws AppException
     */
    public function getCacheAdapter()
    {
        if (!$this->cacheAdapter)
        {
            throw new AppException("No cache adapter object set in controller");
        }

        return $this->cacheAdapter;
    }

    public function setFileService(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Gets the file service passed to the controller
     *
     * @return FileService
     * @throws AppException
     */
    public function getFileService()
    {
        if (!$this->fileService)
        {
            throw new AppException("No file service object set in controller");
        }

        return $this->fileService;
    }

    abstract public function execute();

    protected function setJsonHeader()
    {
        $this->
            getResponse()->
            withHeader('Content-type', 'application/json');
    }
}
