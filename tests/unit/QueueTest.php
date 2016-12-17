<?php

/** 
 * Unit tests for the Queue
 */

use Proximate\Queue;
use Proximate\Service\File as FileService;

class QueueTest extends PHPUnit_Framework_TestCase
{
    const DUMMY_DIR = '/any/dir';
    const DUMMY_URL = 'http://example.com/';
    const DUMMY_HASH = 'a6bf1757fff057f266b697df9cf176fd';

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
        $queue->setUrl($url = self::DUMMY_URL);

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

        $this->assertEquals(self::DUMMY_URL, $queue->getUrl());
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

    /**
     * Checks that a new item is written to a mock FS
     */
    public function testNewQueueItemSucceeds()
    {
        $json = $this->getCacheEntry(self::DUMMY_URL);
        $fileService = $this->getFileServiceMockWithFileExists();
        $fileService->
            shouldReceive('filePutContents')->
            with($this->getQueueEntryPath(), $json)->
            once()
        ;

        $this->getQueueMock($fileService)->
            setUrl(self::DUMMY_URL)->
            queue();
    }

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

    /**
     * @expectedException \Exception
     */
    public function testExistingQueueItemFails()
    {
        // Will fail because a queue item exists already
        $fileService = $this->getFileServiceMockWithFileExists(true);

        $queue = $this->getQueueMock($fileService);
        $queue->
            shouldReceive('createQueueEntry')->
            never();

        $queue->
            setUrl(self::DUMMY_URL)->
            queue();
    }

    public function testProcessor()
    {
        // Set up mocks to return a single item
        $fileService = $this->getFileServiceMock();
        $globPattern = self::DUMMY_DIR . '/*.' . Queue::STATUS_READY;
        $queueItems = [$this->getQueueEntryPath(), ];
        $json = $this->getCacheEntry(self::DUMMY_URL);
        $fileService->

            // Read a list of queue items
            shouldReceive('glob')->
            with($globPattern)->
            andReturn($queueItems)->

            // Read the only queue item
            shouldReceive('fileGetContents')->
            with($queueItems[0])->
            andReturn($json)->

            // Status changes
            shouldReceive('rename')->
            with($this->getQueueEntryPath(), $this->getQueueEntryPath(Queue::STATUS_DOING))->
            once()->
            shouldReceive('rename')->
            with($this->getQueueEntryPath(Queue::STATUS_DOING), $this->getQueueEntryPath(Queue::STATUS_DONE))->
            once()
        ;

        // Set up the queue and process the "waiting" item
        $queue = $this->getQueueMock($fileService);
        $queue->
            shouldReceive('sleep')->
            never();
        $queue->process(1);
    }

    public function testProcessorBadEntry()
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

    protected function getQueueEntryPath($status = Queue::STATUS_READY)
    {
        return self::DUMMY_DIR . '/' . self::DUMMY_HASH . '.' . $status;
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
