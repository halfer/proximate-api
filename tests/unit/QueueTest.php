<?php

/** 
 * Unit tests for the Queue
 */

use Proximate\Queue;

class QueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * Checks that the folder is stored
     */
    public function testConstructorStoresDirectory()
    {
        $dir = __DIR__;
        $queue = new QueueTestHarness($dir);
        $queue->init($dir);

        $this->assertEquals($dir, $queue->getQueueDir());
    }

    public function testConstructorAllowsGoodFolder()
    {
        $dir = __DIR__;
        $queue = new QueueTestHarness($dir);
        $queue->init($dir);

        $this->assertTrue(true);
    }

    /**
     * Emulates a folder not found error
     *
     * @expectedException \Exception
     */
    public function testConstructorRejectsBadFolder()
    {
        $dir = __DIR__;
        $queue = new QueueTestHarness($dir);
        $queue->setDirectoryFound(false);
        $queue->init($dir);
    }

    public function testUrlStorage()
    {
        $url = 'http://example.com/';
        $queue = new QueueTestHarness('');
        $queue->setUrl($url);

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
        $queue = new QueueTestHarness('');

        $this->assertEquals($url, $queue->getUrl());
    }

    public function testUrlRegexStorage()
    {
        $queue = new QueueTestHarness('');

        // Test the empty condition first
        $this->assertNull($queue->getUrlRegex());

        // Now try the setter
        $regex = ".*(/about/careers/.*)|(/job/.*)";
        $queue->setUrlRegex($regex);
        $this->assertEquals($regex, $queue->getUrlRegex());
    }

    public function testRejectFilesStorage()
    {
        $queue = new QueueTestHarness('');

        // Test the initial condition is not null
        $this->assertNotNull($queue->getRejectFiles());

        // Now try the setter
        $reject = "*.png,*.jpg,*.jpeg,*.css,*.js";
        $queue->setRejectFiles($reject);
        $this->assertEquals($reject, $queue->getRejectFiles());
    }

    public function testNewQueueItemSucceeds()
    {
        $queue = $this->getQueueMock();
        $queue->
            shouldReceive('fileExists')->
            andReturn(false)->
            shouldReceive('createQueueEntry');

        $queue->
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
     * @return Queue|\Mockery\Mock
     */
    protected function getQueueMock()
    {
        $dir = __DIR__;
        $queue = Mockery::mock(QueueTestHarness::class, [$dir])->
            makePartial()->
            shouldAllowMockingProtectedMethods();
        $queue->init($dir);

        return $queue;
    }
}

class QueueTestHarness extends Queue
{
    protected $directoryFound = true;

    // Remove the constructor
    public function __construct($queueDir)
    {
    }

    // Make this public
    public function init($queueDir)
    {
        parent::init($queueDir);
    }

    public function setDirectoryFound($directoryFound)
    {
        $this->directoryFound = $directoryFound;
    }

    protected function isDirectory($dir)
    {
        return $this->directoryFound;
    }
}
