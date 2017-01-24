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

    public function getDirectoryChecksDataProvider()
    {
        return [
            [false, true, true, ],
            [true, false, true, ],
            [true, true, false, ],
        ];
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
}
