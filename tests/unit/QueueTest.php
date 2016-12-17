<?php

/** 
 * Unit tests for the Queue
 */

use Proximate\Queue;
use Proximate\Service\File as FileService;

class QueueTest extends PHPUnit_Framework_TestCase
{
    const DUMMY_DIR = '/any/dir';

    /**
     * Checks that the folder is stored
     */
    public function testConstructorStoresDirectory()
    {
        $fileService = Mockery::mock(FileService::class);
        $fileService->
            shouldReceive('isDirectory')->
            andReturn(true);

        $queue = new QueueTestHarness();
        $queue->init($dir = self::DUMMY_DIR, $fileService);

        $this->assertEquals($dir, $queue->getQueueDir());
    }

    public function testConstructorAllowsGoodFolder()
    {
        $fileService = Mockery::mock(FileService::class);
        $fileService->
            shouldReceive('isDirectory')->
            andReturn(true);

        $queue = new QueueTestHarness();
        $queue->init(self::DUMMY_DIR, $fileService);

        $this->assertTrue(true);
    }

    /**
     * Emulates a folder not found error
     *
     * @expectedException \Exception
     */
    public function testConstructorRejectsBadFolder()
    {
        $fileService = Mockery::mock(FileService::class);
        $fileService->
            shouldReceive('isDirectory')->
            andReturn(false);

        $queue = new QueueTestHarness();
        $queue->init(self::DUMMY_DIR, $fileService);
    }

    public function testUrlStorage()
    {
        $url = 'http://example.com/';
        $queue = new QueueTestHarness();
        $queue->setUrl($url, new FileService());

        $this->assertEquals($url, $queue->getUrl());
    }

    /**
     * Ensure that fetching a URL that is not set results in an error
     *
     * @expectedException \Exception
     */
    public function testGetUrlFailsWithNoUrl()
    {
        $url = 'http://example.com/';
        $queue = new QueueTestHarness('', new FileService());

        $this->assertEquals($url, $queue->getUrl());
    }

    public function testUrlRegexStorage()
    {
        $queue = new QueueTestHarness('', new FileService());

        // Test the empty condition first
        $this->assertNull($queue->getUrlRegex());

        // Now try the setter
        $regex = ".*(/about/careers/.*)|(/job/.*)";
        $queue->setUrlRegex($regex);
        $this->assertEquals($regex, $queue->getUrlRegex());
    }

    public function testRejectFilesStorage()
    {
        $queue = new QueueTestHarness('', new FileService());

        // Test the initial condition is not null
        $this->assertNotNull($queue->getRejectFiles());

        // Now try the setter
        $reject = "*.png,*.jpg,*.jpeg,*.css,*.js";
        $queue->setRejectFiles($reject);
        $this->assertEquals($reject, $queue->getRejectFiles());
    }

    public function testNewQueueItemSucceeds()
    {
        $json = '{
    "url": "http:\/\/example.com",
    "url_regex": null,
    "reject_files": "*.png,*.jpg,*.jpeg,*.css,*.js"
}';
        $fileService = Mockery::mock(FileService::class)->makePartial();
        $fileService->
            shouldReceive('fileExists')->
            once()->
            andReturn(false)->

            shouldReceive('filePutContents')->
            with(__DIR__ . '/a9b9f04336ce0181a08e774e01113b31.ready', $json)->
            once()
        ;

        $this->getQueueMock($fileService)->
            setUrl('http://example.com')->
            queue();
    }

    /**
     * @expectedException \Exception
     *
     * @todo This can be refactored together with testNewQueueItemSucceeds
     */
    public function testExistingQueueItemFails()
    {
        $queue = $this->getQueueMock();
        $queue->
            shouldReceive('fileExists')->
            andReturn(true)->
            shouldReceive('createQueueEntry')->
            never();

        $queue->
            setUrl('http://example.com')->
            queue();
    }

    public function testProcessor()
    {
        $this->markTestIncomplete();
    }

    public function testProcessorCallsSleep()
    {
        $this->markTestIncomplete();
    }

    /**
     * @param FileService $fileService
     * @return Queue|\Mockery\Mock
     */
    protected function getQueueMock($fileService = null)
    {
        $dir = __DIR__;
        $queue = Mockery::mock(QueueTestHarness::class)->
            shouldAllowMockingProtectedMethods()->
            makePartial()
        ;
        $queue->init($dir, $fileService ?: new FileService());

        return $queue;
    }

    public function tearDown()
    {
        Mockery::close();
    }
}

class QueueTestHarness extends Queue
{
    // Remove the constructor
    public function __construct()
    {
    }

    // Make this public
    public function init($queueDir, FileService $fileService)
    {
        parent::init($queueDir, $fileService);
    }
}
