<?php

/**
 * Service to hit the proxy reset endpoint
 *
 * The reset endpoint generally looks like:
 *
 * https://container:8083/start?url=http://example.com
 *
 * The parameter should be URL encoded, to ensure any special characters do not cause problems
 */

namespace Proximate\Service;

use Proximate\Exception\RequiredDependency;
use Proximate\Exception\RequiredParam;

class ProxyReset
{
    protected $curl;

    /**
     * Creates the proxy reset service
     *
     * @param \Pest $curl The curl module to use (containing a URL base)
     */
    public function __construct(\Pest $curl)
    {
        $this->init($curl);
    }

    public function init(\Pest $curl)
    {
        $this->curl = $curl;
    }

    /**
     * The new URL for the proxy to record on
     *
     * @todo Check the JSON response and fail if it indicates a problem
     *
     * @param string $url
     * @return string
     */
    public function resetRecorder($url)
    {
        if (!$url)
        {
            throw new RequiredParam(
                "An URL parameter is required to reset the proxy"
            );
        }

        $responseBody = $this->getCurl()->post('/start?url=' . urlencode($url), []);

        // Wait for the bounced server to settle
        $this->sleep();

        return $responseBody;
    }

    /**
     * This sends a restart to the WireMock URL in the current cURL
     *
     * To do the same bounce on the console, simply fire this off:
     *
     * curl --data '' http://proximate-proxy:8082/__admin/shutdown
     *
     * @return string
     */
    public function resetWiremockProxy()
    {
        $responseBody = $this->getCurl()->post('__admin/shutdown', []);

        // Wait for the bounced server to settle
        $this->sleep();

        return $responseBody;
    }

    protected function sleep()
    {
        sleep(5);
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
