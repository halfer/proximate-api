<?php

/**
 * Unit tests for exceptions in the cache installer service
 */

namespace Proximate\Test;

use Proximate\Exception\NotWritable as NotWritableException;

// @todo Add test parents to the autoloader
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
            andThrow(new NotWritableException());

        $this->
            getCacheCopier()->
            execute();
    }

    /**
     * @expectedException Proximate\Exception\NotWritable
     */
    public function testCopyMappingsFailure()
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
            // It blows up here
            getFileService()->
            shouldReceive('filePutContents')->
            once()->
            andThrow(new NotWritableException());

        $cacheCopier->execute();
    }

    /**
     * Very similar to CacheCopierTest::testCopyMappings
     *
     * See CacheCopierTest::addDeleteSourceFoldersExpectation for all the write ops
     * that should be able to throw a NotWritable exception. I'm only testing one of them
     * as they should all behave in the same fashion.
     *
     * @expectedException Proximate\Exception\NotWritable
     */
    public function testDeleteUrlFolderFailure()
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
            addMappingPutFileExpectation()->
            // It blows up here
            getFileService()->
            shouldReceive('unlinkFiles')->
            with(self::DUMMY_RECORD_SITE_FILES_DIR)->
            once()->
            andThrow(new NotWritableException());

        $cacheCopier->execute();
    }
}
