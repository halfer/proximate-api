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
        $this->assertEquals(['action' => 'getCountController', ], $this->getJson($page));
    }

    /**
     * @todo Work out how to get around annoying PHP web server "dot" bug
     * @driver simple
     */
    public function testCountUrlRouting()
    {
        $url = 'http://www.example.com/';
        $page = $this->pageVisit(self::BASE_URL . '/count/' . urlencode($url));
        $this->assertEquals(
            ['action' => 'getCountUrlController', 'url' => $url, ],
            $this->getJson($page)
        );

    }

    /**
     * @driver simple
     *
     * @todo Add tests for page and size (with defaults as appropriate)
     */
    public function testCacheListRouting()
    {
        $page = $this->pageVisit(self::BASE_URL . '/list');
        $this->assertEquals(
            ['action' => 'getCacheListController', ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     *
     * @todo Add tests for page and size (with defaults as appropriate)
     */
    public function testCacheListRoutingWithPage()
    {
        $page = $this->pageVisit(self::BASE_URL . '/list/1');
        $this->assertEquals(
            ['action' => 'getCacheListController', ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     *
     * @todo Add tests for page and size (with defaults as appropriate)
     */
    public function testCacheListRoutingWithPageAndSize()
    {
        $page = $this->pageVisit(self::BASE_URL . '/list/1/10');
        $this->assertEquals(
            ['action' => 'getCacheListController', ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function testCacheSave()
    {
        $page = $this->pageVisit(self::BASE_URL . '/cache', 'POST');
        $this->assertEquals(
            ['queue' => 'Proximate\Queue\Write', 'action' => 'getCacheSaveController', ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function testItemStatusRouting()
    {
        $page = $this->pageVisit(self::BASE_URL . '/status/1');
        $this->assertEquals(
            ['action' => 'getItemStatusController', ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function __testItemDeleteRouting()
    {
        $page = $this->pageVisit(self::BASE_URL . '/cache/1', 'DELETE');
        $this->assertEquals(
            ['action' => 'getItemStatusController', ],
            $this->getJson($page)
        );
    }

    protected function getJson(Page $page)
    {
        $json = $page->text();
        $data = json_decode($json, true);

        return $data;
    }

    /**
     * Attempts a visit wrapped up in a try-catch block
     *
     * @param string $uri
     * @return Page
     */
    protected function pageVisit($uri, $method = 'GET')
    {
        try
        {
            $page = $this->driver()->request($method, $uri)->page();
        }
        catch (Exception_Curl $e)
        {
            $page = null;
            $this->fail($e->getMessage());
        }

        return $page;
    }
}
