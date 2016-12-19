<?php

/**
 * Base for unit tests
 */

namespace Proximate\Test;

use Mockery;
use Proximate\Service\File as FileService;
use Proximate\Queue\Base as Queue;

abstract class QueueTestBase extends \PHPUnit_Framework_TestCase
{
    const DUMMY_DIR = '/any/dir';
    const DUMMY_URL = 'http://example.com/';
    const DUMMY_HASH = 'a6bf1757fff057f266b697df9cf176fd';

    /**
     * This test runs in both QueueRead and QueueWrite contexts
     */
    public function testInitFileService()
    {
        $fileService = $this->getFileServiceMock();
        $queue = $this->getQueueMock($fileService);
        $queue->init(self::DUMMY_DIR, $fileService);
        $this->assertEquals($fileService, $queue->getFileService());
    }

    /**
     * @param string $queueClassName
     * @param FileService $fileService
     * @return Queue|\Mockery\Mock
     */
    protected function getQueueMock($queueClassName, $fileService = null)
    {
        $queue = Mockery::mock($queueClassName)->
            shouldAllowMockingProtectedMethods()->
            makePartial();
        $queue->init(self::DUMMY_DIR, $fileService ?: new FileService());

        return $queue;
    }

    protected function getFileServiceMock($isDirectory = true)
    {
        $fileService = Mockery::mock(FileService::class);
        $fileService->
            shouldReceive('isDirectory')->
            andReturn($isDirectory);

        return $fileService;
    }

    abstract protected function getQueueTestHarness();

    /**
     * Creates a JSON string representing a cache entry
     *
     * @param string $url
     * @return string
     */
    protected function getCacheEntry($url)
    {
$json = '{
    "url": __URL__,
    "url_regex": null,
    "reject_files": "*.png,*.jpg,*.jpeg,*.css,*.js"
}';
        $out = str_replace('__URL__', json_encode($url), $json);

        return $out;
    }

    protected function getQueueEntryPath($status = Queue::STATUS_READY)
    {
        return self::DUMMY_DIR . '/' . self::DUMMY_HASH . '.' . $status;
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
