<?php

/**
 * Site fetcher service
 */

namespace Proximate\Service;

use Proximate\Exception\SiteFetch as SiteFetchException;
use Proximate\SimpleCrawler;

class SiteFetcher
{
    protected $proxyAddress;
    protected $lastLog;

    public function __construct($proxyAddress)
    {
        $this->setProxyAddress($proxyAddress);
    }

    public function execute($startUrl, $pathRegex)
    {
        $crawler = new SimpleCrawler($this->proxyAddress);
        $crawler->
            init()->
            crawl($startUrl, $pathRegex);

        // FIXME how do we know it failed?
        if (false)
        {
            throw new SiteFetchException(
                "There was a problem with the site fetch call"
            );
        }
    }

    /**
     * Useful for testing (when the constructor is not called)
     *
     * @param string $proxyAddress
     */
    public function setProxyAddress($proxyAddress)
    {
        $this->proxyAddress = $proxyAddress;
    }
}
