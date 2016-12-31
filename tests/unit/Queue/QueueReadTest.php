<?php

/** 
 * Unit tests for reading from the Queue
 */

namespace Proximate\Test;

use Proximate\Queue\Read as Queue;
use Proximate\Queue\Write as QueueWrite;
use Proximate\Service\File as FileService;
use Proximate\Service\SiteFetcher as FetcherService;
use Proximate\Exception\SiteFetch as SiteFetchException;

class QueueReadTest extends QueueTestBase
{
    protected $fetcherService;
    protected $resetService;

    /**
     * Check that the read-specific setter stores the fetcher OK
     */
    public function testSetFetcher()
    {
        $this->getFileServiceMock();

        $queue = $this->createQueueReadMock();
        $fetcherService = $this->getFetcherService();
        $this->assertEquals($fetcherService, $queue->getSiteFetcherService());
    }

    public function testProcessor()
    {
        $this->initFileServiceMockWithOneEntry();

        // Specify expected status changes
        $this->setRenameExpectations(Queue::STATUS_DONE);

        // Set up a mock to emulate the fetcher
        $this->fetcherService->
            shouldReceive('execute')->
            with(
                self::DUMMY_URL,
                null,
                QueueWrite::DEFAULT_REJECT_FILES
            )->
            once();

        // Set up the queue and process the "waiting" item
        $this->processOneItem();
    }

    /**
     * Ensures that a failed fetch results in a status change to error
     */
    public function testProcessorWithFetchFail()
    {
        $this->initFileServiceMockWithOneEntry();

        // Specify expected status changes
        $this->setRenameExpectations(Queue::STATUS_ERROR);

        // Set up a mock to emulate the fetcher
        $fetchService = \Mockery::mock(FetcherService::class);
        $fetchService->
            shouldReceive('execute')->
            andThrow(new SiteFetchException());

        // Set up the queue and process the "waiting" item
        $this->processOneItem();
    }

    /**
     * Ensures that a proxy reset call failure results in a status change to error
     */
    public function testProcessorWithProxyResetFail()
    {
        // @todo New unit test
        $this->markTestIncomplete();
    }

    /**
     * Checks that an invalid entry is renamed
     */
    public function testProcessorBadEntry()
    {
        // Set up mocks to return a single item
        $fileService = $this->getFileServiceMock();
        $queueItems = [$this->getQueueEntryPath(), ];

        $this->setGlobExpectation($queueItems);
        $fileService->

            // Read the only queue item
            shouldReceive('fileGetContents')->
            with($queueItems[0])->
            once()->
            andReturn("Bad JSON");

        // Check that the rename is called in the right way
        $this->setOneRenameExpectation(Queue::STATUS_READY, Queue::STATUS_INVALID);

        // Set up the queue and process the corrupted item
        $this->processOneItem(1);
    }

    public function testProcessorOnEmptyQueue()
    {
        // Set up mocks to return a single item
        $fileService = $this->getFileServiceMock();
        $queueItems = [];

        $this->setGlobExpectation($queueItems);
        $fileService->

            // Should not read anything
            shouldReceive('fileGetContents')->
            never()->

            // No status changes
            shouldReceive('rename')->
            never();

        $this->initFetcherMockNeverCalled();

        // Set up the queue and process zero items
        $this->processOneItem(1);
    }

    protected function processOneItem($sleepCount = 0)
    {
        $queue = $this->createQueueReadMock();
        $queue->
            shouldReceive('sleep')->
            times($sleepCount);
        $queue->process(1);
    }

    /**
     * Gets a mocked class for the file service
     *
     * @return \Mockery\Mock|FileService
     */
    protected function initFileServiceMockWithOneEntry()
    {
        // Set up mocks to return a single item
        $fileService = $this->getFileServiceMock();
        $queueItems = [$this->getQueueEntryPath(), ];

        $this->setGlobExpectation($queueItems);
        $fileService->

            // Read the only queue item
            shouldReceive('fileGetContents')->
            with($queueItems[0])->
            once()->
            andReturn($this->getCacheEntry(self::DUMMY_URL));

        return $fileService;
    }

    protected function setGlobExpectation(array $queueItems)
    {
        $globPattern = self::DUMMY_DIR . '/*.' . Queue::STATUS_READY;
        $this->
            getFileService()->
            shouldReceive('glob')->
            with($globPattern)->
            andReturn($queueItems);
    }

    protected function setRenameExpectations($endStatus)
    {
        $this->setOneRenameExpectation(Queue::STATUS_READY, Queue::STATUS_DOING);
        $this->setOneRenameExpectation(Queue::STATUS_DOING, $endStatus);
    }

    protected function setOneRenameExpectation($startStatus, $endStatus)
    {
        $this->
            getFileService()->
            shouldReceive('rename')->
            with($this->getQueueEntryPath($startStatus), $this->getQueueEntryPath($endStatus))->
            once();
    }

    protected function getQueueTestHarness()
    {
        return new QueueReadTestHarness();
    }

    /**
     * Gets a mock of the system under test
     *
     * @return \Mockery\Mock|QueueReadTestHarness
     */
    protected function createQueueReadMock()
    {
        $queue = parent::getQueueMock(QueueReadTestHarness::class);
        $queue->setFetcher($this->getFetcherService());

        return $queue;
    }

    protected function initFetcherMockNeverCalled()
    {
        $this->
            getFetcherService()->
            shouldReceive('execute')->
            never();
    }

    protected function setUp()
    {
        $this->fetcherService = \Mockery::mock(FetcherService::class);
        parent::setUp();
    }

    protected function getFetcherService()
    {
        if (!$this->fetcherService)
        {
            throw new \Exception();
        }

        return $this->fetcherService;
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
