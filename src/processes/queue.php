#!/usr/bin/env php
<?php

/**
 * Launches the queue, will be restarted by Supervisor when it finishes
 *
 * @todo Create an integration test for the queue
 */

use Proximate\Service\File;
use Proximate\Service\SiteFetcher as SiteFetcherService;
use Proximate\Queue\Read as QueueReader;

$root = realpath(__DIR__ . '/../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

$commands = ['a' => 'address', 'p' => 'path', ];
$actions = getopt('a:p:', ['address:', 'path:']);

$queuePath = isset($actions['path']) ? $actions['path'] : (isset($actions['p']) ? $actions['p'] : null);
$proxyAddress = isset($actions['address']) ? $actions['address'] : (isset($actions['a']) ? $actions['a'] : null);

if (!$queuePath || !$proxyAddress)
{
    $command = __FILE__;
    die(
        sprintf("Syntax: %s --address <api> --path <queue-path>\n", $command)
    );
}

if (!file_exists($queuePath))
{
    die(
        sprintf("Error: the supplied queue path `%s` does not exist\n", $queuePath)
    );
}

$queue = new QueueReader($queuePath, new File());
$queue->
    setFetcher(new SiteFetcherService($proxyAddress))->
    process();
