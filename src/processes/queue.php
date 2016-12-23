<?php

/**
 * Launches the queue, will be restarted by Supervisor when it finishes
 */

use Proximate\Service\File;
use Proximate\Service\SiteFetcher;

$root = realpath(__DIR__ . '/../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

$queue = new Proximate\Queue\Read(
    '/var/proximate/queue',
    new File(),
    new SiteFetcher()
);
$queue->process();
