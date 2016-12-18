<?php

/** 
 * Unit tests for writing to the Queue
 *
 * @todo The tests testConstructorStoresDirectory, testConstructorAllowsGoodFolder and
 * testConstructorRejectsBadFolder should be moved to the base so they are run for
 * the Read tests as well.
 */

namespace Proximate\Test;

use Proximate\Queue\Write as Queue;
use Proximate\Service\File as FileService;

class QueueWriteTest extends QueueTestBase
{
    /**
     * Checks that the folder is stored
     */
    public function testConstructorStoresDirectory()
    {
        $queue = new QueueWriteTestHarness();
        $queue->init($dir = self::DUMMY_DIR, $this->getFileServiceMock());

        $this->assertEquals($dir, $queue->getQueueDir());
    }

    public function testConstructorAllowsGoodFolder()
    {
        $queue = new QueueWriteTestHarness();
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
        $queue = new QueueWriteTestHarness();
        $queue->init(self::DUMMY_DIR, $this->getFileServiceMock(false));
    }

    public function testUrlStorage()
    {
        // Doesn't need full initialisation
        $queue = new QueueWriteTestHarness();
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
        $queue = new QueueWriteTestHarness();

        $this->assertEquals(self::DUMMY_URL, $queue->getUrl());
    }

    public function testUrlRegexStorage()
    {
        // Doesn't need full initialisation
        $queue = new QueueWriteTestHarness();

        // Test the empty condition first
        $this->assertNull($queue->getUrlRegex());

        // Now try the setter
        $regex = ".*(/about/careers/.*)|(/job/.*)";
        $queue->setUrlRegex($regex);
        $this->assertEquals($regex, $queue->getUrlRegex());
    }

    public function testRejectFilesStorage()
    {
        $queue = new QueueWriteTestHarness('', new FileService());

        // Test the initial condition has the default value
        $this->assertEquals(Queue::DEFAULT_REJECT_FILES, $queue->getRejectFiles());

        // Now try the setter to something different
        $reject = "*.js";
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

    protected function getQueueMock($fileService = null)
    {
        return parent::getQueueMock(QueueWriteTestHarness::class, $fileService);
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
}

class QueueWriteTestHarness extends Queue
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
