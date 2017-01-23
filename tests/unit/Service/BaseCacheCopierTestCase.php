<?php

/**
 * Shared test methods for the two sets of cache copier tests
 */

namespace Proximate\Test;

use Proximate\Service\CacheCopier as CacheCopierService;
use Proximate\Service\File as FileService;

class BaseCacheCopierTestCase extends \PHPUnit_Framework_TestCase
{
    const DUMMY_RECORD_DIR = '/cache/record';
    const DUMMY_PLAY_DIR = '/cache/play';
    const DUMMY_PLAY_FILES_DIR = '/cache/play/__files';
    const DUMMY_PLAY_MAPPINGS_DIR = '/cache/play/mappings';

    protected $fileService;

    public function setUp()
    {
        parent::setUp();

        $this->fileService = \Mockery::mock(FileService::class);
    }

    protected function setBasePathValidationExpectations($recordPathExists = true, $playPathExists = true)
    {
        $this->
            setIsDirectoryExpectation(self::DUMMY_RECORD_DIR, $recordPathExists)->
            setIsDirectoryExpectation(self::DUMMY_PLAY_DIR, $playPathExists);

        return $this;
    }

    protected function setPlaybackPathCheckExpectations(
        $playFilesExists = true, $playMappingsExists = true, $exceptions = false
    ) {
        $this->
            setIsDirectoryExpectation(self::DUMMY_PLAY_FILES_DIR, $playFilesExists)->
            setIsDirectoryExpectation(self::DUMMY_PLAY_MAPPINGS_DIR, $playMappingsExists);

        // If a directory does not exist, it should be created, and vice versa,
        // unless we're throwing an exception
        $this->
            setMkdirExpectation(self::DUMMY_PLAY_FILES_DIR, $playFilesExists ? 0 : 1, $exceptions)->
            setMkdirExpectation(self::DUMMY_PLAY_MAPPINGS_DIR, $playMappingsExists ? 0 : 1, $exceptions);

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

    /**
     * @todo Can this be simplified to the base case, with andThrow() optionally tacked on
     * the end?
     *
     * @param string $path
     * @param integer $times
     * @param boolean $exceptions
     * @return $this
     */
    protected function setMkdirExpectation($path, $times, $exceptions)
    {
        $fileService = $this->getFileService();
        if ($exceptions)
        {
            $fileService->
                shouldReceive('mkdir')->
                with($path)->
                times($times)->
                andThrow(new \Proximate\Exception\NotWritable("Look, a squirrel!"));
        }
        else
        {
            $fileService->
                shouldReceive('mkdir')->
                with($path)->
                times($times);
        }

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
    
    protected function getCacheCopier(
        $recordCachePath = self::DUMMY_RECORD_DIR,
        $playCachePath = self::DUMMY_PLAY_DIR)
    {
        $service = new CacheCopierService($this->getFileService(), $recordCachePath, $playCachePath);
        $service->setLogging(false);

        return $service;
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
