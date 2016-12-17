<?php

/** 
 * Unit tests for reading from the Queue
 */

namespace Proximate\Test;

use Proximate\Queue\Read as Queue;

class QueueReadTest extends QueueTestBase
{
    public function __testProcessor()
    {
        // Set up mocks to return a single item
        $fileService = $this->getFileServiceMock();
        $globPattern = self::DUMMY_DIR . '/*.' . Queue::STATUS_READY;
        $queueItems = [$this->getQueueEntryPath(), ];
        $fileService->

            // Read a list of queue items
            shouldReceive('glob')->
            with($globPattern)->
            andReturn($queueItems)->

            // Read the only queue item
            shouldReceive('fileGetContents')->
            with($queueItems[0])->
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

    public function __testProcessorBadEntry()
    {
        $this->markTestIncomplete();
    }

    public function __testProcessorOnEmptyQueue()
    {
        // Set up mocks to return a single item
        $fileService = $this->getFileServiceMock();
        $globPattern = self::DUMMY_DIR . '/*.' . Queue::STATUS_READY;
        $queueItems = [];

        $fileService->

            // Read a list of queue items
            shouldReceive('glob')->
            with($globPattern)->
            andReturn($queueItems)->

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
}
