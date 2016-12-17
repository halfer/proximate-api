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
        $queue = new QueueTestHarness();
        $queue->init($dir = self::DUMMY_DIR, $this->getFileServiceMock());

        $this->assertEquals($dir, $queue->getQueueDir());
    }

    public function testConstructorAllowsGoodFolder()
    {
        $queue = new QueueTestHarness();
        $queue->init(self::DUMMY_DIR, $this->getFileServiceMock());

        $this->assertTrue(true);
    }

    /**
     * Emulates a folder not found error
     *
     * @expectedException \Exception
     */
    public function testConstructorRejectsBadFolder()
    {
        $queue = new QueueTestHarness();
        $queue->init(self::DUMMY_DIR, $this->getFileServiceMock(false));
    }

    public function testUrlStorage()
    {
        // Doesn't need full initialisation
        $queue = new QueueTestHarness();
        $queue->setUrl($url = 'http://example.com/');

        $this->assertEquals($url, $queue->getUrl());
    }

    /**
     * Ensure that fetching a URL that is not set results in an error
     *
     * @expectedException \Exception
     */
    public function testGetUrlFailsWithNoUrl()
    {
        $queue = new QueueTestHarness();

        $this->assertEquals('http://example.com/', $queue->getUrl());
    }

    public function testUrlRegexStorage()
    {
        // Doesn't need full initialisation
        $queue = new QueueTestHarness();

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

        // Test the initial condition has a non-null default value
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
        $fileService = $this->getFileServiceMockWithFileExists();
        $fileService->
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
     */
    public function testExistingQueueItemFails()
    {
        $fileService = $this->getFileServiceMockWithFileExists();
        $fileService->
            shouldReceive('fileExists')->
            andReturn(true);

        $queue = $this->getQueueMock($fileService);
        $queue->
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
        $queue = Mockery::mock(QueueTestHarness::class)->
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

    protected function getFileServiceMockWithFileExists($fileExists = false)
    {
        $fileService = $this->getFileServiceMock();
        $fileService->
            shouldReceive('fileExists')->
            once()->
            andReturn($fileExists);

        return $fileService;
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
