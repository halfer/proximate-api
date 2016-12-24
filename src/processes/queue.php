<?php

/**
 * Launches the queue, will be restarted by Supervisor when it finishes
 *
 * @todo Might be worth sending the proxy address in via an env var, and reading it here?
 */

use Proximate\Service\File;
use Proximate\Service\SiteFetcher;

$root = realpath(__DIR__ . '/../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

$queue = new Proximate\Queue\Read(
    '/var/proximate/queue',
    new File(),
    new SiteFetcher('proximate-proxy:8081')
);
$queue->process();
