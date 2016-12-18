<?php

/**
 * Tests for the wget service class
 */

use Proximate\Service\SiteFetcher;

class SiteFetcherTest extends PHPUnit_Framework_TestCase
{
    public function testWithUrlOnly()
    {
        $url = "http://example.com/";
        $expectedCommand = "
            wget \
                --recursive \\
                --wait 3 \\
                --limit-rate=20K \\
                --delete-after \\
                -e use_proxy=yes \\
                -e http_proxy=127.0.0.1:8082 \\
                {$url}";

        $siteFetcher = $this->getFetcherService($expectedCommand);
        $siteFetcher->execute($url, null, null);
    }

    public function testWithUrlAndUrlRegex()
    {
        // @todo This requires some tests
        $this->markTestIncomplete();
    }

    public function testWithUrlAndRejectFiles()
    {
        // @todo This requires some tests
        $this->markTestIncomplete();
    }

    protected function getFetcherService($expectedCommand)
    {
        $siteFetcher = Mockery::mock(SiteFetcher::class)->
            makePartial()->
            shouldAllowMockingProtectedMethods();
        $siteFetcher->
            shouldReceive('runCommand')->
            with($expectedCommand)->
            once()->
            // This prevents unexpected output actually running the command :)
            shouldReceive('runCommand')->
            withAnyArgs()->
            never();

        return $siteFetcher;
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
