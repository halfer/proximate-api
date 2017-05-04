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
    const DUMMY_ID = '282790cd-a154-31fc-8e41-60ad3a0d154a';
    const CACHE_ADAPTER_CLASS = 'Proximate\\Storage\\Filesystem';

    // Currently using the script name to get around a dot bug in the PHP web server
    #const BASE_URL = 'http://localhost:10000';
    const BASE_URL = 'http://localhost:10000/index.php';

    /**
     * @driver simple
     */
    public function testCountRouting()
    {
        $page = $this->pageVisit(self::BASE_URL . '/count');
        $this->assertEquals(
            ['action' => 'getCountController', 'cache_adapter' => self::CACHE_ADAPTER_CLASS, ],
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
        $page = $this->pageVisit(self::BASE_URL . '/list');
        $this->assertEquals(
            ['action' => 'getCacheListController', 'page' => 1,
                'pagesize' => 10, 'cache_adapter' => self::CACHE_ADAPTER_CLASS, ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function testCacheListRoutingWithPage()
    {
        $pageNo = 3;
        $page = $this->pageVisit(self::BASE_URL . "/list/$pageNo");
        $this->assertEquals(
            ['action' => 'getCacheListController', 'page' => $pageNo,
                'pagesize' => 10, 'cache_adapter' => self::CACHE_ADAPTER_CLASS, ],
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
        $page = $this->pageVisit(self::BASE_URL . "/list/$pageNo/$pageSize");
        $this->assertEquals(
            ['action' => 'getCacheListController', 'page' => $pageNo,
                'pagesize' => $pageSize, 'cache_adapter' => self::CACHE_ADAPTER_CLASS ],
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
            ['queue' => 'Proximate\\Queue\\Write', 'action' => 'getCacheSaveController', ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function testItemFetchRouting()
    {
        $guid = self::DUMMY_ID;
        $page = $this->pageVisit(self::BASE_URL . '/cache/' . urlencode($guid));
        $this->assertEquals(
            [
                'action' => 'getItemGetController', 'guid' => $guid,
                'cache_adapter' => self::CACHE_ADAPTER_CLASS,
            ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function testItemDeleteRouting()
    {
        $guid = self::DUMMY_ID;
        $page = $this->pageVisit(self::BASE_URL . '/cache/' . urlencode($guid), 'DELETE');
        $this->assertEquals(
            [
                'action' => 'getItemDeleteController', 'guid' => $guid,
                'cache_adapter' => self::CACHE_ADAPTER_CLASS,
            ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function testProxyLogRouting()
    {
        $page = $this->pageVisit(self::BASE_URL . '/log');
        $this->assertEquals(
            [
                'action' => 'getProxyLogController',
                'log_path' => '/remote/cache/proxy.log',
            ],
            $this->getJson($page)
        );
    }

    /**
     * @driver simple
     */
    public function testQueueListRouting()
    {
        $page = $this->pageVisit(self::BASE_URL . '/queue/ready');
        $this->assertEquals(
            [
                'action' => 'getQueueListController',
                'status' => 'ready',
                'queue_path' => '/var/proximate/queue',
                'file_service' => 'Proximate\\Service\\File'
            ],
            $this->getJson($page)
        );
    }

    protected function getJson(Page $page)
    {
        echo "Getting JSON:\n";
        $json = $page->text();
        echo $json . "\n";
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
