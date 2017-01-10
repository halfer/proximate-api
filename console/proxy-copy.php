<?php

/**
 * A mappings installer for the playback system
 *
 * Modifies the mappings files to include a header Host filter
 *
 * @todo Keep a track of files written, as there is a possibility of filename clash
 */

$pathRecord = "/remote/cache/record";
$folders = glob($pathRecord . '/*');
foreach ($folders as $urlFolder)
{
    if (checkFolder($urlFolder))
    {
        checkFolder($urlFolder);
    }
}

/**
 * Checks if a path to a folder, e.g. /remote/cache/record/www_example_com can be processed
 *
 * @param string $urlFolder
 */
function checkFolder($urlFolder)
{
    return
        is_dir($urlFolder) &&
        is_dir(getMappingsFolder($urlFolder)) &&
        is_dir(getFilesFolder($urlFolder));
}

function getMappingsFolder($urlFolder)
{
    return $urlFolder . '/mappings';
}

function getFilesFolder($urlFolder)
{
    return $urlFolder . '/__files';
}

/**
 * Accepts a path to a folder, e.g. /remote/cache/record/www_example_com for processing
 *
 * @param string $urlFolder
 */
function processFolder($urlFolder)
{
    copyFiles($urlFolder);
    copyMappings($urlFolder);
}

function copyFiles($urlFolder)
{
    $files = getFilesFolder($urlFolder);
    system("cp {$files}/* /remote/cache/playback/`");
}

/**
 * Copies all JSON mappings
 *
 * @param string $urlFolder
 */
function copyMappings($urlFolder)
{
    $mappingsFiles = glob(getMappingsFolder($urlFolder) . '/*');
    foreach ($mappingsFiles as $mappingFile)
    {
        copyMapping($mappingFile);
    }
}

/**
 * Copies a single JSON mapping file and adds a header
 *
 * @todo Fix the example.com URL with a real one
 * @param string $mappingFile
 */
function copyMapping($mappingFile)
{
    $json = file_get_contents($mappingFile);
    $data = json_decode($json, true);

    // Add in a host header
    $data['request']['headers'] = ['Host' => 'http://www.example.com/',];

    $leafName = md5($data) . '.json';
    $jsonAgain = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents('/remote/cache/playback/mappings/' . $leafName, $jsonAgain);
}
