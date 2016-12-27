<?php

/**
 * Launches the queue, will be restarted by Supervisor when it finishes
 *
 * @todo Might be worth sending the proxy address in via an env var, and reading it here?
 */

use Proximate\Service\File;
use Proximate\Service\SiteFetcher as SiteFetcherService;
use Proximate\Service\ProxyReset as ProxyResetService;

$root = realpath(__DIR__ . '/../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

$proxyResetCurl = new PestJSON('http://proximate-proxy:8083');
$queue = new Proximate\Queue\Read(
    '/var/proximate/queue',
    new File()
);
$queue->
    setFetcher(new SiteFetcherService('proximate-proxy:8081'))->
    setProxyResetter(new ProxyResetService($proxyResetCurl))->
    process();
