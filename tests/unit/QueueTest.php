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
        // @todo Will need to mock out file_exists from checkEntryExists first
        $this->markTestIncomplete();
    }

    public function testExistingQueueItemFails()
    {
        $this->markTestIncomplete();
    }

    public function testProcessor()
    {
        $this->markTestIncomplete();
    }

    public function checkProcessorCallsSleep()
    {
        $this->markTestIncomplete();
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
