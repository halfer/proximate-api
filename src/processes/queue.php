<?php

/**
 * Launches the queue, will be restarted by Supervisor when it finishes
 *
 * @todo How can I test whether the below code works? e.g. if the constructors correct?
 * @todo Might be worth sending the proxy addresses in via an env var, and reading it here?
 */

use Proximate\Service\File;
use Proximate\Service\SiteFetcher as SiteFetcherService;

$root = realpath(__DIR__ . '/../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

$curlApi = new PestJSON('http://proximate-proxy:8083');
$curlPlayback = new PestJSON('http://proximate-proxy:8082');
$queue = new Proximate\Queue\Read(
    '/var/proximate/queue',
    new File()
);
$queue->
    setFetcher(new SiteFetcherService('proximate-proxy:8081'))->
    process();
