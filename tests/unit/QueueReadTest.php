<?php

/** 
 * Unit tests for reading from the Queue
 */

namespace Proximate\Test;

use Proximate\Queue\Read as Queue;
use Proximate\Queue\Write as QueueWrite;
use Proximate\Service\File as FileService;
use Proximate\Service\SiteFetcher as FetcherService;

class QueueReadTest extends QueueTestBase
{
    /**
     * Check that the read-specific initFetcher stores the fetcher OK
     */
    public function testInitFetcher()
    {
        $fileService = $this->getFileServiceMock();
        $queue = $this->getQueueMock($fileService);
        $fetcherService = \Mockery::mock(FetcherService::class);
        $queue->initFetcher($fetcherService);
        $this->assertEquals($fetcherService, $queue->getSiteFetcherService());
    }

    public function testProcessor()
    {
        // Set up mocks to return a single item
        $fileService = $this->getFileServiceMock();
        $queueItems = [$this->getQueueEntryPath(), ];

        $this->setGlobExpectation($fileService, $queueItems);
        $fileService->

            // Read the only queue item
            shouldReceive('fileGetContents')->
            with($queueItems[0])->
            once()->
            andReturn($this->getCacheEntry(self::DUMMY_URL))->

            // Status changes
            shouldReceive('rename')->
            with($this->getQueueEntryPath(), $this->getQueueEntryPath(Queue::STATUS_DOING))->
            once()->
            shouldReceive('rename')->
            with($this->getQueueEntryPath(Queue::STATUS_DOING), $this->getQueueEntryPath(Queue::STATUS_DONE))->
            once()
        ;

        // Set up a mock to emulate the fetcher
        $fetchService = \Mockery::mock(FetcherService::class);
        $fetchService->
            shouldReceive('execute')->
            with(
                self::DUMMY_URL,
                null,
                QueueWrite::DEFAULT_REJECT_FILES
            )->
            once();

        // Set up the queue and process the "waiting" item
        $queue = $this->getQueueMock($fileService);
        $queue->initFetcher($fetchService);
        $queue->
            shouldReceive('sleep')->
            never();
        $queue->process(1);
    }

    /**
     * @expectedException \Proximate\Exception\InvalidQueueItem
     */
    public function testProcessorBadEntry()
    {
        // Set up mocks to return a single item
        $fileService = $this->getFileServiceMock();
        $queueItems = [$this->getQueueEntryPath(), ];

        $this->setGlobExpectation($fileService, $queueItems);
        $fileService->

            // Read the only queue item
            shouldReceive('fileGetContents')->
            with($queueItems[0])->
            once()->
            andReturn("Bad JSON");

        // Set up the queue and process the corrupted item
        $queue = $this->getQueueMock($fileService);
        $queue->initFetcher($this->getFetcherMockNeverCalled());
        $queue->
            shouldReceive('sleep')->
            never();
        $queue->process(1);
    }

    public function testProcessorOnEmptyQueue()
    {
        // Set up mocks to return a single item
        $fileService = $this->getFileServiceMock();
        $queueItems = [];

        $this->setGlobExpectation($fileService, $queueItems);
        $fileService->

            // Should not read anything
            shouldReceive('fileGetContents')->
            never()->

            // No status changes
            shouldReceive('rename')->
            never();

        // Set up the queue and process zero items
        $queue = $this->getQueueMock($fileService);
        $queue->initFetcher($this->getFetcherMockNeverCalled());
        $queue->
            shouldReceive('sleep')->
            once();
        $queue->process(1);
    }

    protected function setGlobExpectation(FileService $fileService, array $queueItems)
    {
        $globPattern = self::DUMMY_DIR . '/*.' . Queue::STATUS_READY;
        $fileService->
            shouldReceive('glob')->
            with($globPattern)->
            andReturn($queueItems);
    }

    /**
     * Gets a mock of the system under test
     *
     * @param FileService $fileService
     * @return \Mockery\Mock|QueueReadTestHarness
     */
    protected function getQueueMock($fileService)
    {
        return parent::getQueueMock(QueueReadTestHarness::class, $fileService);
    }

    protected function getFetcherMockNeverCalled()
    {
        $fetchService = \Mockery::mock(FetcherService::class);
        $fetchService->
            shouldReceive('execute')->
            never();

        return $fetchService;
    }
}

class QueueReadTestHarness extends Queue
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

    // Make this public
    public function initFetcher(FetcherService $fetcherService)
    {
        return parent::initFetcher($fetcherService);
    }

    // Make this public
    public function getFileService()
    {
        return parent::getFileService();
    }

    // Make this public
    public function getSiteFetcherService()
    {
        return parent::getSiteFetcherService();
    }
}
