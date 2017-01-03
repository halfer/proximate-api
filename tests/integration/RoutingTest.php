<?php

/**
 * Functional test for routing in a web server context
 */

namespace Proximate\Test\Integration;

use halfer\SpiderlingUtils\TestCase;
use Openbuildings\Spiderling\Page;
use Openbuildings\Spiderling\Exception_Curl;

class RoutingTest extends TestCase
{
    // Currently using the script name to get around a dot bug in the PHP web server
    #const BASE_URL = 'http://localhost:10000';
    const BASE_URL = 'http://localhost:10000/index.php';

    /**
     * @driver simple
     */
    public function testCountRouting()
    {
        $page = $this->pageVisit(self::BASE_URL . '/count');
        $this->assertEquals([], $this->getJson($page));
    }

    /**
     * @todo Work out how to get around annoying PHP web server "dot" bug
     * @driver simple
     */
    public function testCountUrlRouting()
    {
        $url = 'http://www.example.com/';
        $page = $this->pageVisit(self::BASE_URL . '/count/' . urlencode($url));
        $this->assertEquals(['url' => $url, ], $this->getJson($page));

    }

    public function testCacheListRouting()
    {
        $this->markTestIncomplete();
    }

    /**
     * @todo Convert this to pageVisit so any 404 gremlins are trapped
     * @driver simple
     */
    public function testCacheSave()
    {
        /* @var $driver \Openbuildings\Spiderling\Driver_Simple */
        $driver = $this->driver()->post(self::BASE_URL . '/cache');
        $page = $driver->page();
        $this->assertEquals(['queue' => 'Proximate\Queue\Write', ], $this->getJson($page));
    }

    public function testItemStatusRouting()
    {
        $this->markTestIncomplete();
    }

    public function testItemDeleteRouting()
    {
        $this->markTestIncomplete();
    }

    protected function getJson(Page $page)
    {
        $json = $page->text();
        $data = json_decode($json, true);

        return $data;
    }

    protected function pageVisit($uri)
    {
        try
        {
            $page = $this->visit($uri);
        }
        catch (Exception_Curl $e)
        {
            $page = null;
            $this->fail($e->getMessage());
        }

        return $page;
    }
}
