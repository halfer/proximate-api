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

$root = realpath(__DIR__ . '/..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

$recordCachePath = "/remote/cache/record";
$playCachePath = "/remote/cache/playback";

$cacheCopier = new CacheCopierService(new FileService(), $recordCachePath, $playCachePath);
$cacheCopier->execute();
