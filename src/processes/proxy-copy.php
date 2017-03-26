<?php

/**
 * A mappings installer for the playback system
 *
 * Modifies the mappings files to include a header Host filter
 */

use Proximate\Service\CacheCopier as CacheCopierService;
use Proximate\Service\File as FileService;
use Proximate\Service\ProxyReset;

$root = realpath(__DIR__ . '/../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

$recordCachePath = "/remote/cache/record";
$playCachePath = "/remote/cache/playback";

// Copy the newly recorded sites to the player
$fileService = new FileService('/tmp/proxy-copy.log');
$ok = tryCopyingWithRetries($fileService, $recordCachePath, $playCachePath);

if ($ok)
{
    echo "Wait for servers to settle...\n";
    sleep(5);

    // Restart the player by calling the proxy restart endpoint directly
    echo "Restarting proxy...\n";
    $curlApi = new PestJSON('http://proximate-proxy:8083');
    $curlPlayback = new PestJSON('http://proximate-proxy:8082');
    $resetter = new ProxyReset($curlApi, $curlPlayback);
    $resetter->resetWiremockProxy();

    // If we got this far, wait for a while before allowing Supervisor to run this again
    echo "Sleeping...\n";
    sleep(120);
}
else
{
    echo "Giving up due to repeated failures\n";
    exit(1);
}

function tryCopyingWithRetries(FileService $fileService, $recordCachePath, $playCachePath)
{
    $iter = 0;
    do
    {
        $iter++;
        try
        {
            $cacheCopier = new CacheCopierService($fileService, $recordCachePath, $playCachePath);
            $cacheCopier->execute();
            $ok = true;
        }
        catch (\Exception $e)
        {
            $ok = false;
            error_log(
                sprintf(
                    "Failed to copy cache files (try %d): %s\n",
                    $iter,
                    $e->getMessage()
                )
            );
            sleep(120);
        }
    } while (!$ok && $iter < 5);

    return $ok;
}
