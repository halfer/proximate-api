<?php

/**
 * A mappings installer for the playback system
 *
 * Modifies the mappings files to include a header Host filter
 *
 * @todo Test this script after wiring in the CacheCopier
 * @todo Can I run a real scraper on the proxy?
 * @todo When running this script, clear out old mappings, or back them up somewhere
 * @todo Keep a track of files written, as there is a possibility of filename clash
 */

use Proximate\Service\CacheCopier as CacheCopierService;
use Proximate\Service\File as FileService;
use Proximate\Service\ProxyReset;

$root = realpath(__DIR__ . '/..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

$recordCachePath = "/remote/cache/record";
$playCachePath = "/remote/cache/playback";

// Copy the newly recorded sites to the player
$cacheCopier = new CacheCopierService(new FileService(), $recordCachePath, $playCachePath);
$cacheCopier->execute();

// Restart the player by calling the proxy restart endpoint directly
echo "Restarting proxy...\n";
$curl = new PestJSON('http://proximate-proxy:8082');
$resetter = new ProxyReset($curl);
$resetter->resetWiremockProxy();

// If we got this far, wait for a while before allowing Supervisor to run this again
echo "Sleeping...\n";
sleep(120);
