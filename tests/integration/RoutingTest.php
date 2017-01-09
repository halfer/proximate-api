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
    const DUMMY_URL = 'http://www.example.com/';

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
        $url = self::DUMMY_URL;
        $page = $this->pageVisit(self::BASE_URL . '/count/' . urlencode($url));
        $this->assertEquals(
            ['action' => 'getCountUrlController', 'url' => $url, ],
            $this->getJson($page)
        );

    }

    /**
     * @driver simple
     *
     * @todo Use a dataprovider to supply testCacheListRouting/testCacheListRoutingWithPage/testCacheListRoutingWithPageAndSize
     * with URLs for play/record
     */
    public function testCacheListRouting()
    {
        $page = $this->pageVisit(self::BASE_URL . '/record/list');
        $this->assertEquals(
            ['action' => 'getCacheListController', 'page' => 1, 'pagesize' => 10, ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function testCacheListRoutingWithPage()
    {
        $pageNo = 3;
        $page = $this->pageVisit(self::BASE_URL . "/record/list/$pageNo");
        $this->assertEquals(
            ['action' => 'getCacheListController', 'page' => $pageNo, 'pagesize' => 10, ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function testCacheListRoutingWithPageAndSize()
    {
        $pageNo = 4;
        $pageSize = 15;
        $page = $this->pageVisit(self::BASE_URL . "/record/list/$pageNo/$pageSize");
        $this->assertEquals(
            ['action' => 'getCacheListController', 'page' => $pageNo, 'pagesize' => $pageSize, ],
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
        $guid = 1;
        $page = $this->pageVisit(self::BASE_URL . "/status/$guid");
        $this->assertEquals(
            ['action' => 'getItemStatusController', 'guid' => $guid, ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function testItemDeleteRouting()
    {
        $url = self::DUMMY_URL;
        $page = $this->pageVisit(self::BASE_URL . '/cache/' . urlencode($url), 'DELETE');
        $this->assertEquals(
            ['action' => 'getItemDeleteController', 'url' => $url, ],
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
