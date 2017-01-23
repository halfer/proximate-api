<?php

/**
 * Unit tests for exceptions in the cache installer service
 */

namespace Proximate\Test;

use Proximate\Exception\NotWritable;
#use Proximate\Service\CacheCopier as CacheCopierService;
#use Proximate\Service\File as FileService;

require_once 'BaseCacheCopierTestCase.php';

class CacheCopierExceptionsTest extends BaseCacheCopierTestCase
{
    /**
     * @expectedException \Proximate\Exception\DirectoryNotFound
     */
    public function testRecordCacheDirectoryFails()
    {
        $this->
            setBasePathValidationExpectations(false, true);
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
            setBasePathValidationExpectations(true, false);
        $this->
            getCacheCopier()->
            execute();
    }

    /**
     * Checks that a failing mkdir raises the expected exception
     *
     * @dataProvider missingPlaybackFolderDataProvider
     * @expectedException \Proximate\Exception\NotWritable
     */
    public function testFailsToCreatePlaybackFolder($playFilesExists, $playMappingsExists)
    {
        $this->
            setGlobExpectation(self::DUMMY_RECORD_DIR . '/*')->
            setBasePathValidationExpectations()->
            setPlaybackPathCheckExpectations($playFilesExists, $playMappingsExists, true);
        $this->
            getCacheCopier()->
            execute();
    }

    public function missingPlaybackFolderDataProvider()
    {
        return [
            [false, true],
            [true, false],
        ];
    }

    /**
     * @expectedException Proximate\Exception\NotWritable
     */
    public function testCopyFilesFailure()
    {
        $this->setStandardSearchExpectations();
        $this->setFolderVerificationExpectations();
        $this->
            getFileService()->
            shouldReceive('copy')->
            with(self::DUMMY_RECORD_SITE_FILES_DIR . '/*', self::DUMMY_PLAY_FILES_DIR)->
            once()->
            andThrow(new NotWritable());

        $this->
            getCacheCopier()->
            execute();
    }

    // WIP
    public function __testCopyMappingsFailure()
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

        // Work up to the file put contents
        $this->
            addfindMappingsGlobExpectation($mappingPath)->
            addMappingGetFileExpectation($mappingPath)->
            addDomainFileExistsExpectation($domainPath)->
            addDomainGetFileExpectation($domainPath)->
            // @todo Replace this with a call to addMappingPutFileExpectation
            getFileService()->
            shouldReceive('filePutContents')->
            once()->
            andThrow(new NotWritable());

        $this->
            getCacheCopier()->
            execute();
    }

    public function testDeleteUrlFolderFailure()
    {
        $this->markTestIncomplete();
    }
}
