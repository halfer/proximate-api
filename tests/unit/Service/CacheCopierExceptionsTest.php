<?php

/**
 * Unit tests for exceptions in the cache installer service
 */

namespace Proximate\Test;

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
}
