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

class ProxyReset
{
    protected $curl;
    protected $resetUrl;

    public function __construct(\Pest $curl, $resetUrl)
    {
        $this->curl = $curl;
        $this->resetUrl = $resetUrl;
    }

    /**
     * The new URL for the proxy to record on
     *
     * @todo Throw a catchable error if the URL is empty
     *
     * @param string $url
     */
    public function execute($url)
    {
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
