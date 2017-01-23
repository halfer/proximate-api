<?php

/**
 * Unit tests for the cache installer service
 */

namespace Proximate\Test;

use Proximate\Service\CacheCopier as CacheCopierService;
use Proximate\Service\File as FileService;

require_once 'BaseCacheCopierTestCase.php';

class CacheCopierTest extends BaseCacheCopierTestCase
{
    const DUMMY_RECORD_SITE_DIR = '/cache/record/http_www_example_com';
    const DUMMY_RECORD_SITE_DOMAIN = '/cache/record/http_www_example_com/domain.txt';
    const DUMMY_RECORD_SITE_FILES_DIR = '/cache/record/http_www_example_com/__files';
    const DUMMY_RECORD_SITE_MAPPINGS_DIR = '/cache/record/http_www_example_com/mappings';

    public function testCacheDirectoriesExist()
    {
        $this->
            setGlobExpectation(self::DUMMY_RECORD_DIR . '/*')->
            setBasePathValidationExpectations()->
            setPlaybackPathCheckExpectations();
        $this->
            getCacheCopier()->
            execute();
        $this->assertTrue(true);
    }

    public function testCreatePlaybackFilesFolderIfRequired()
    {
        $this->
            setGlobExpectation(self::DUMMY_RECORD_DIR . '/*')->
            setBasePathValidationExpectations()->
            setPlaybackPathCheckExpectations(false, true);
        $this->
            getCacheCopier()->
            execute();
    }

    public function testCreatePlaybackMappingsFolderIfRequired()
    {
        $this->
            setGlobExpectation(self::DUMMY_RECORD_DIR . '/*')->
            setBasePathValidationExpectations()->
            setPlaybackPathCheckExpectations(true, false);
        $this->
            getCacheCopier()->
            execute();
    }

    /**
     * @dataProvider getDirectoryChecksDataProvider
     */
    public function testCopyCacheCheckFolderFails($urlOk, $mapOk, $filesOk)
    {
        $this->setStandardSearchExpectations();
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
        $this->setStandardSearchExpectations();
        $this->setFolderVerificationExpectations();
        $cacheCopier = $this->getCacheCopierMock();
        $cacheCopier->
            shouldAllowMockingProtectedMethods()->
            shouldReceive('copyFiles')->
            once()->
            shouldReceive('copyMappings')->
            once();
        $this->addDeleteSourceFoldersExpectation();

        $cacheCopier->execute();
    }

    public function testCopyFiles()
    {
        $this->setStandardSearchExpectations();
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
        $this->addDeleteSourceFoldersExpectation();
        $cacheCopier->execute();
    }

    public function testCopyMappings()
    {
        $this->setStandardSearchExpectations();
        $this->setFolderVerificationExpectations();
        $cacheCopier = $this->getCacheCopierMock();

        // Ignore things in `copyFiles` for the moment
        $cacheCopier->
            shouldAllowMockingProtectedMethods()->
            shouldReceive('copyFiles');

        // Here's some details about the files we're working with
        $mappingPath = self::DUMMY_RECORD_SITE_MAPPINGS_DIR . '/mapping1.json';
        $domainPath = self::DUMMY_RECORD_SITE_DIR . '/domain.txt';

        // Here is the main test
        $this->
            addfindMappingsGlobExpectation($mappingPath)->
            addMappingGetFileExpectation($mappingPath)->
            addDomainFileExistsExpectation($domainPath)->
            addDomainGetFileExpectation($domainPath)->
            addMappingPutFileExpectation()->
            addDeleteSourceFoldersExpectation();
        $cacheCopier->execute();
    }

    protected function addfindMappingsGlobExpectation($mappingPath)
    {
        $this->
            getFileService()->
            shouldReceive('glob')->
            with(self::DUMMY_RECORD_SITE_MAPPINGS_DIR . '/*')->
            andReturn([$mappingPath, ])->
            once();

        return $this;
    }

    protected function addMappingGetFileExpectation($mappingPath)
    {
        $this->
            getFileService()->
            shouldReceive('fileGetContents')->
            with($mappingPath)->
            andReturn($this->getExampleMapping(false))->
            once();

        return $this;
    }

    protected function addDomainFileExistsExpectation($domainPath)
    {
        $this->
            getFileService()->
            shouldReceive('fileExists')->
            with($domainPath)->
            andReturn(true)->
            once();

        return $this;
    }

    protected function addDomainGetFileExpectation($domainPath)
    {
        $this->
            getFileService()->
            shouldReceive('fileGetContents')->
            with($domainPath)->
            andReturn('http://www.example.com/')->
            once();

        return $this;
    }

    protected function addMappingPutFileExpectation()
    {
        $this->
            getFileService()->
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

        return $this;
    }

    protected function addDeleteSourceFoldersExpectation()
    {
        $this->
            getFileService()->
            shouldReceive('unlinkFiles')->
            with(self::DUMMY_RECORD_SITE_FILES_DIR)->
            once()->
            shouldReceive('unlinkFiles')->
            with(self::DUMMY_RECORD_SITE_MAPPINGS_DIR)->
            once()->
            shouldReceive('rmDir')->
            with(self::DUMMY_RECORD_SITE_FILES_DIR)->
            once()->
            shouldReceive('rmDir')->
            with(self::DUMMY_RECORD_SITE_MAPPINGS_DIR)->
            once()->
            shouldReceive('unlinkFile')->
            with(self::DUMMY_RECORD_SITE_DOMAIN)->
            once()->
            shouldReceive('rmDir')->
            with(self::DUMMY_RECORD_SITE_DIR)->
            once();

        return $this;
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

    protected function setStandardSearchExpectations()
    {
        $files = [self::DUMMY_RECORD_SITE_DIR, ];
        $this->
            setGlobExpectation(self::DUMMY_RECORD_DIR . '/*', $files)->
            setBasePathValidationExpectations()->
            setPlaybackPathCheckExpectations();

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
        $mock->setLogging(false);

        return $mock;
    }
}
