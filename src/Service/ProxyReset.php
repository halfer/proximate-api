<?php

/** 
 * Service to hit the proxy reset endpoint
 *
 * The reset endpoint generally looks like:
 *
 * https://container:8083/start?url=http://example.com
 */

namespace Proximate\Service;

use Proximate\Exception\RequiredDependency;
use Proximate\Exception\RequiredParam;

class ProxyReset
{
    protected $curl;
    protected $resetUrl;

    /**
     * Creates the proxy reset service
     *
     * @param \Pest $curl The curl module to use
     * @param string $resetUrl The full endpoint to hit to register a reset
     */
    public function __construct(\Pest $curl, $resetUrl)
    {
        $this->curl = $curl;
        $this->resetUrl = $resetUrl;
    }

    /**
     * The new URL for the proxy to record on
     *
     * @param string $url
     */
    public function execute($url)
    {
        if (!$url)
        {
            throw new RequiredParam(
                "An URL parameter is required to reset the proxy"
            );
        }

        $this->getCurl()->post($this->resetUrl, ['url' => $url, ]);
    }

    /**
     * Gets the currently set curl module
     *
     * @return \Pest
     * @throws RequiredDependency
     */
    protected function getCurl()
    {
        if (!$this->curl)
        {
            throw new RequiredDependency(
                "cURL module not set"
            );
        }

        return $this->curl;
    }
}
