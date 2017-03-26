<?php

/**
 * Tests for the wget service class
 */

namespace Proximate\Test;

use Proximate\Service\SiteFetcher;

class SiteFetcherTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_URL = 'http://example.com/';
    const DUMMY_HTTP_PROXY = '127.0.0.1:8082';
    const DUMMY_HTTPS_PROXY = '127.0.0.1:8083';

    public function testWithUrlOnly()
    {
        $this->checkWithUrlOnly(true);
    }

    /**
     * Ensures that a failure can be detected
     *
     * @expectedException Proximate\Exception\SiteFetch
     */
    public function testWithError()
    {
        $this->checkWithUrlOnly(false);
    }

    protected function checkWithUrlOnly($ok)
    {
        $url = self::DUMMY_URL;
        $expectedCommand = $this->getCommandPrefix() . "
                -e use_proxy=yes \\
                -e http_proxy=127.0.0.1:8082 \\
                -e https_proxy=127.0.0.1:8083 \\
                {$url}";

        $siteFetcher = $this->getFetcherService($expectedCommand, $ok);
        $siteFetcher->execute($url, null, null);
    }

    public function testWithUrlAndUrlRegex()
    {
        $url = self::DUMMY_URL;
        $regex = "*\.html";
        $expectedCommand = $this->getCommandPrefix() . "
                --accept-regex \"{$regex}\" \\
                -e use_proxy=yes \\
                -e http_proxy=127.0.0.1:8082 \\
                -e https_proxy=127.0.0.1:8083 \\
                {$url}";

        $siteFetcher = $this->getFetcherService($expectedCommand);
        $siteFetcher->execute($url, $regex, null);
    }

    public function testWithUrlAndRejectFiles()
    {
        $url = self::DUMMY_URL;
        $reject = "*.js,*.jpeg,*.jpg";
        $expectedCommand = $this->getCommandPrefix() . "
                --reject \"{$reject}\" \\
                -e use_proxy=yes \\
                -e http_proxy=127.0.0.1:8082 \\
                -e https_proxy=127.0.0.1:8083 \\
                {$url}";

        $siteFetcher = $this->getFetcherService($expectedCommand);
        $siteFetcher->execute($url, null, $reject);
    }

    public function getCommandPrefix()
    {
        return "
            wget \
                --output-file /tmp/wget.log \\
                --directory-prefix=/tmp/wget/ \\
                --recursive \\
                --wait 3 \\
                --limit-rate=20K \\
                --delete-after \\
                --no-directories \\";
    }

    protected function getFetcherService($expectedCommand, $ok = true)
    {
        $siteFetcher = \Mockery::mock(SiteFetcher::class)->
            makePartial()->
            shouldAllowMockingProtectedMethods();
        $siteFetcher->setProxies(self::DUMMY_HTTP_PROXY, self::DUMMY_HTTPS_PROXY);
        $siteFetcher->
            shouldReceive('runCommand')->
            with($expectedCommand)->
            once()->
            andReturn($ok)->
            // This prevents unexpected output actually running the command :)
            shouldReceive('runCommand')->
            withAnyArgs()->
            never();

        return $siteFetcher;
    }

    public function tearDown()
    {
        \Mockery::close();
    }
}
