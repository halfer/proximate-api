<?php

/**
 * Unit tests for the cache installer service
 */

namespace Proximate\Test;

use Proximate\Service\CacheCopier as CacheCopierService;
use Proximate\Service\File as FileService;

class CacheCopierTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_RECORD_DIR = '/cache/record';
    const DUMMY_PLAY_DIR = '/cache/play';

    protected $fileService;
    
    public function setUp()
    {
        parent::setUp();

        $this->fileService = \Mockery::mock(FileService::class);
    }

    public function testCacheDirectoriesExist()
    {
        $this->
            setGlobExpectation(self::DUMMY_RECORD_DIR . '/*')->
            setIsDirectoryExpectation(self::DUMMY_RECORD_DIR)->
            setIsDirectoryExpectation(self::DUMMY_PLAY_DIR);
        $this->
            getCacheCopier()->
            execute();
        $this->assertTrue(true);
    }

    /**
     * @expectedException \Proximate\Exception\DirectoryNotFound
     */
    public function testRecordCacheDirectoryFails()
    {
        $this->
            setIsDirectoryExpectation(self::DUMMY_RECORD_DIR, false)->
            setIsDirectoryExpectation(self::DUMMY_PLAY_DIR);
        $this->
            getCacheCopier()->
            execute();
    }

    /**
     * @expectedException \Proximate\Exception\DirectoryNotFound
     */
    public function testPlaybackCacheDirectoryFails()
    {
        $this->
            setIsDirectoryExpectation(self::DUMMY_RECORD_DIR)->
            setIsDirectoryExpectation(self::DUMMY_PLAY_DIR, false);
        $this->
            getCacheCopier()->
            execute();
    }

    public function testCopyCacheCheckFolderFails()
    {
        $this->getStandardFolderExpectations();
        $this->
            getCacheCopier()->
            execute();
    }

    protected function getStandardFolderExpectations()
    {
        $files = ['file', ];
        $this->
            setGlobExpectation(self::DUMMY_RECORD_DIR . '/*', $files)->
            setIsDirectoryExpectation(self::DUMMY_RECORD_DIR)->
            setIsDirectoryExpectation(self::DUMMY_PLAY_DIR);

        return $this;
    }

    protected function setIsDirectoryExpectation($path, $return = true)
    {
        $this->
            getFileService()->
            shouldReceive('isDirectory')->
            with($path)->
            andReturn($return);

        return $this;
    }

    protected function setGlobExpectation($path, $return = [])
    {
        $this->
            getFileService()->
            shouldReceive('glob')->
            with($path)->
            andReturn($return);

        return $this;
    }

    protected function getCacheCopier($recordCachePath = self::DUMMY_RECORD_DIR, $playCachePath = self::DUMMY_PLAY_DIR)
    {
        return new CacheCopierService($this->getFileService(), $recordCachePath, $playCachePath);
    }

    /**
     * 
     * @return \Mockery\Mock|FileService
     */
    protected function getFileService()
    {
        return $this->fileService;
    }
}
