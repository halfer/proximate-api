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
    const DUMMY_PLAY_FILES_DIR = '/cache/play/__files';
    const DUMMY_PLAY_MAPPINGS_DIR = '/cache/play/mappings';
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
        $cacheCopier = $this->getCacheCopierMock();
        $cacheCopier->
            shouldAllowMockingProtectedMethods()->
            shouldReceive('processFolder')->
            never();
        $cacheCopier->execute();
    }

    public function testCopyCacheCheckFolderSucceeds()
    {
        $this->getStandardSearchExpectations();
        $this->setFolderVerificationExpectations();
        $cacheCopier = $this->getCacheCopierMock();
        $cacheCopier->
            shouldAllowMockingProtectedMethods()->
            shouldReceive('copyFiles')->
            once()->
            shouldReceive('copyMappings')->
            once();
        $cacheCopier->execute();
    }

    public function testCopyFiles()
    {
        $this->getStandardSearchExpectations();
        $this->setFolderVerificationExpectations();
        $cacheCopier = $this->getCacheCopierMock();

        // Ignore things in `copyMappings` for the moment
        $cacheCopier->
            shouldAllowMockingProtectedMethods()->
            shouldReceive('copyMappings');

        // Here is the main test
        $this->
            getFileService()->
            shouldReceive('copy')->
            with(self::DUMMY_RECORD_SITE_FILES_DIR . '/*', self::DUMMY_PLAY_FILES_DIR)->
            once();
        $cacheCopier->execute();
    }

    public function testCopyMappings()
    {
        $this->getStandardSearchExpectations();
        $this->setFolderVerificationExpectations();
        $cacheCopier = $this->getCacheCopierMock();

        // Ignore things in `copyFiles` for the moment
        $cacheCopier->
            shouldAllowMockingProtectedMethods()->
            shouldReceive('copyFiles');

        // Here's some details about the mapping file we copy-and-modify
        $mappingPath = self::DUMMY_RECORD_SITE_MAPPINGS_DIR . '/mapping1.json';

        // Here is the main test
        $this->
            getFileService()->
            // Search for mappings files
            shouldReceive('glob')->
            with(self::DUMMY_RECORD_SITE_MAPPINGS_DIR . '/*')->
            andReturn([$mappingPath, ])->
            once()->
            // Get the single mapping
            shouldReceive('fileGetContents')->
            with($mappingPath)->
            andReturn($this->getExampleMapping(false))->
            once()->
            // See if a domain file exists
            shouldReceive('fileExists')->
            with($domainPath = self::DUMMY_RECORD_SITE_DIR . '/domain.txt')->
            andReturn(true)->
            once()->
            // Return the contents of a domain file
            shouldReceive('fileGetContents')->
            with($domainPath)->
            andReturn('http://www.example.com/')->
            once()->
            // Write the mapping file containing the updated mapping file
            shouldReceive('filePutContents')->
            withArgs(
                // Check that $pathname starts with the play mappings path
                // and ends with ".json"
                function($pathName, $json)
                {
                    $prefix = preg_quote(self::DUMMY_PLAY_MAPPINGS_DIR);
                    $pathOk = preg_match("#^{$prefix}.*\.json$#", $pathName);
                    $jsonOk = $json === $this->getExampleMapping(true);
                    return $pathOk && $jsonOk;
                }
            )->
            once();
        $cacheCopier->execute();
    }

    /**
     * Returns a partial mapping file in JSON
     *
     * @return string
     */
    protected function getExampleMapping($withHost)
    {
        $mapping = [
            'request' => [
                'url' => '/about',
                'method' => 'GET'
            ]
        ];
        if ($withHost)
        {
            $mapping['request']['headers'] = [
                'Host' => ['equalTo' => 'www.example.com', ],
            ];
        }

        return json_encode($mapping, JSON_PRETTY_PRINT);
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

    protected function getCacheCopier(
        $recordCachePath = self::DUMMY_RECORD_DIR,
        $playCachePath = self::DUMMY_PLAY_DIR)
    {
        return new CacheCopierService($this->getFileService(), $recordCachePath, $playCachePath);
    }

    /**
     * Gets a partial mock of the SUT
     *
     * @param FileService $fileService
     * @param string $recordCachePath
     * @param string $playCachePath
     * @return \Mockery\Mock|CacheCopierService
     */
    protected function getCacheCopierMock(
        $recordCachePath = self::DUMMY_RECORD_DIR,
        $playCachePath = self::DUMMY_PLAY_DIR)
    {
        // Make a partial mock on the copier
        $mock = \Mockery::mock(CacheCopierService::class)->makePartial();
        $mock->init($this->getFileService(), $recordCachePath, $playCachePath);

        return $mock;
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
