<?php

/**
 * Functional test for API
 *
 * Not all func tests here could be expressed using Spiderling (i.e. post, delete endpoints).
 * Thus I have used Curl as well, which is more flexible.
 *
 * @todo Convert Spiderling tests to Curl so just one approach is used
 * @todo Swap from SpiderlingUtils\TestCase to PHPUnit TestCase
 */

namespace Proximate\Test\Integration;

use halfer\SpiderlingUtils\TestCase;
use Curl\Curl;

class ApiTest extends TestCase
{
    const BASE_URL = 'http://localhost:10001/index.php';
    const EXAMPLE_CACHE_KEY = '18b4ddb061a95760ec6c58f4c4dc037f54614da2';

    protected $curlClient;

    /**
     * Reset the queue to ensure tests do not become serially dependent
     */
    public function setUp()
    {
        $this->resetCache();
        $this->createEmptyQueue();
        $this->initCurl();

        parent::setUp();
    }

    /**
     * Checks that the 404 response looks good
     */
    public function testNonExistentEndpoint()
    {
        $client = $this->
            getCurlClient()->
            get(self::BASE_URL . '/cockadoodledoo');
        $this->assertEquals(404, $client->http_status_code);
        $this->assertEquals(
            ['result' => ['ok' => false, 'error' => 'Endpoint not found']],
            json_decode($client->response, true)
        );
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
        $json = $this->visit(self::BASE_URL . '/cache/' . self::EXAMPLE_CACHE_KEY)->text();
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
                        'key' => self::EXAMPLE_CACHE_KEY,
                    ]
                ]
            ],
            $data
        );
    }

    /**
     * Checks that a cache item can be deleted by the API
     */
    public function testDeleteCacheItem()
    {
        $client = $this->
            getCurlClient()->
            delete(self::BASE_URL . '/cache/' . self::EXAMPLE_CACHE_KEY);
        $this->assertEquals(
            ['result' => ['ok' => 1, ]],
            json_decode($client->response, true)
        );

        // Let's confirm the new count (should be 2 minus the 1 deleted item)
        $countJson = $client->
            reset()->
            get(self::BASE_URL . '/count')->
            response;
        $countData = json_decode($countJson, true);
        $this->assertEquals(1, $countData['result']['count']);
    }

    /**
     * Create an entry in the crawler queue
     *
     * @todo Move queue and cache paths to consts
     */
    public function testQueueItem()
    {
        $client = $this->getCurlClient()->post(
            self::BASE_URL . '/cache',
            json_encode([
                'url' => 'http://example.com/hullo',
                'path_regex' => '.*',
            ])
        );

        // Make sure we have an OK response and a new entry in the queue
        $queueData = json_decode($client->response, true);
        $this->assertEquals(
            ['result' => ['ok' => true, ]],
            $queueData
        );
        $this->assertEquals(
            1,
            count(glob('/tmp/proximate-tests/queue/*'))
        );
    }

    public function testQueueItemBadUrl()
    {
        $this->markTestIncomplete();
    }

    public function testQueueItemBadRegex()
    {
        $this->markTestIncomplete();
    }

    /**
     * Creates a pristine copy of a test cache for every test
     */
    protected function resetCache()
    {
        $cacheSource = realpath(__DIR__ . '/../cache');
        $testCache = '/tmp/proximate-tests/cache-read';
        system("mkdir --parents {$testCache}");
        system("rm -f {$testCache}/*");
        system("cp {$cacheSource}/* {$testCache}/");
    }

    protected function createEmptyQueue()
    {
        $testQueue = '/tmp/proximate-tests/queue';
        system("mkdir --parents {$testQueue}");
        system("rm -f {$testQueue}/*");
    }

    /**
     * Set up a new curl client
     */
    protected function initCurl()
    {
        $this->curlClient = new Curl();
    }

    /**
     * Gets the current curl instance
     *
     * @return Curl
     */
    protected function getCurlClient()
    {
        return $this->curlClient;
    }
}
