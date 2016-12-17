<?php

/** 
 * Unit tests for reading from the Queue
 */

namespace Proximate\Test;

use Proximate\Queue\Read as Queue;
use Proximate\Service\File as FileService;

class QueueReadTest extends QueueTestBase
{
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

        // Set up the queue and process the "waiting" item
        $queue = $this->getQueueMock($fileService);
        $queue->
            shouldReceive('sleep')->
            never();
        $queue->process(1);
    }

    /**
     * @expectedException \Exception
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

    protected function getQueueMock($fileService)
    {
        return parent::getQueueMock(QueueReadTestHarness::class, $fileService);
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
}
