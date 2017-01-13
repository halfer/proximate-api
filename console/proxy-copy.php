<?php

/**
 * A mappings installer for the playback system
 *
 * Modifies the mappings files to include a header Host filter
 *
 * @todo Can I run a real scraper on the proxy?
 * @todo When running this script, clear out old mappings, or back them up somewhere
 * @todo Move this logic into a service
 * @todo Write some unit tests around this
 * @todo Keep a track of files written, as there is a possibility of filename clash
 */

$pathRecord = "/remote/cache/record";
$folders = glob($pathRecord . '/*');
foreach ($folders as $urlFolder)
{
    if (checkFolder($urlFolder))
    {
        processFolder($urlFolder);
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
    system("cp {$files}/* /remote/cache/playback/__files");
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
        copyMapping($urlFolder, $mappingFile);
    }
}

/**
 * Copies a single JSON mapping file and adds a header
 *
 * @param string $mappingFile
 */
function copyMapping($urlFolder, $mappingFile)
{
    // Read the data from the mapping file
    $json = file_get_contents($mappingFile);
    $data = json_decode($json, true);

    // Add in a host header
    $data['request']['headers'] = [
        'Host' => ['equalTo' => getSiteDomain($urlFolder), ]
    ];

    $leafName = md5($json) . '.json';
    $jsonAgain = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents('/remote/cache/playback/mappings/' . $leafName, $jsonAgain);
}

/**
 * Reads the domain file give a domain folder
 *
 * @todo Swap the general exception for something more specific
 *
 * @param string $urlFolder
 * @throws \Exception
 */
function getSiteDomain($urlFolder)
{
    $domainFile = $urlFolder . '/domain.txt';
    if (!file_exists($domainFile))
    {
        throw new \Exception(
            "Site domain not found"
        );
    }

    return trim(file_get_contents($domainFile));
}
