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
    const DUMMY_RECORD_SITE_DIR = '/cache/record/http_www_example_com';
    const DUMMY_RECORD_SITE_FILES_DIR = '/cache/record/http_www_example_com/__files';
    const DUMMY_RECORD_SITE_MAPPINGS_DIR = '/cache/record/http_www_example_com/mappings';

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

    /**
     * @dataProvider getDirectoryChecksDataProvider
     */
    public function testCopyCacheCheckFolderFails($urlOk, $mapOk, $filesOk)
    {
        $this->getStandardSearchExpectations();
        $this->setFolderVerificationExpectations($urlOk, $mapOk, $filesOk);
        $this->
            getCacheCopier()->
            execute();
        // Ensure that process was called zero times
    }

    public function getDirectoryChecksDataProvider()
    {
        return [
            [false, true, true, ],
            [true, false, true, ],
            [true, true, false, ],
        ];
    }

    protected function getStandardSearchExpectations()
    {
        $files = [self::DUMMY_RECORD_SITE_DIR, ];
        $this->
            setGlobExpectation(self::DUMMY_RECORD_DIR . '/*', $files)->
            setIsDirectoryExpectation(self::DUMMY_RECORD_DIR)->
            setIsDirectoryExpectation(self::DUMMY_PLAY_DIR);

        return $this;
    }

    protected function setFolderVerificationExpectations($urlOk = true, $mapOk = true, $filesOk = true)
    {
        $this->
            setIsDirectoryExpectation(self::DUMMY_RECORD_SITE_DIR, $urlOk)->
            setIsDirectoryExpectation(self::DUMMY_RECORD_SITE_MAPPINGS_DIR, $mapOk)->
            setIsDirectoryExpectation(self::DUMMY_RECORD_SITE_FILES_DIR, $filesOk);

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
