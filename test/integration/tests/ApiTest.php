<?php

/**
 * Functional test for API
 */

namespace Proximate\Test\Integration;

use halfer\SpiderlingUtils\TestCase;

class ApiTest extends TestCase
{
    const BASE_URL = 'http://localhost:10001/index.php';

    /**
     * Reset the queue to ensure tests do not become serial
     */
    public function setUp()
    {
        $cacheSource = realpath(__DIR__ . '/../cache');
        $testCache = '/tmp/proximate-tests/cache-read';
        system("mkdir --parents {$testCache}");
        system("rm {$testCache}/*");
        system("cp {$cacheSource}/* {$testCache}/");

        parent::setUp();
    }

    public function testNonExistentEndpoint()
    {
        // @todo
        $this->markTestIncomplete();
    }

    /**
     * @driver simple
     *
     * I've just counted the number of results here to reduce test brittleness - however
     * if there is a need, it could be changed to an equality test on the whole array.
     */
    public function testFetchPage()
    {
        $json = $this->visit(self::BASE_URL . '/list')->text();
        $data = json_decode($json, true);
        $this->assertEquals(true, $data['result']['ok']);
        $this->assertEquals(2, count($data['result']['list']));
    }

    /**
     * @driver simple
     */
    public function testFetchCount()
    {
        $json = $this->visit(self::BASE_URL . '/count')->text();
        $data = json_decode($json, true);
        $this->assertEquals(
            $data,
            ['result' => ['ok' => true, 'count' => 2, ]]
        );
    }

    /**
     * @driver simple
     */
    public function testFetchCacheItem()
    {
        $key = '18b4ddb061a95760ec6c58f4c4dc037f54614da2';
        $json = $this->visit(self::BASE_URL . '/cache/' . $key)->text();
        $data = json_decode($json, true);

        // I'm removing the response just to reduce test brittleness
        unset($data['result']['item']['response']);

        $this->assertEquals(
            [
                'result' => [
                    'ok' => true,
                    'item' => [
                        'url' => 'http://127.0.0.1:23306/test.html',
                        'method' => 'POST',
                        'key' => $key,
                    ]
                ]
            ],
            $data
        );
    }

    /**
     * @driver simple
     */
    public function testDeleteCacheItem()
    {
        // @todo
        $this->markTestIncomplete();
    }

    /**
     * @driver simple
     */
    public function testQueueItem()
    {
        // @todo
        $this->markTestIncomplete();
    }
}
