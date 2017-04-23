#!/usr/bin/env php
<?php

/**
 * Launches the queue, will be restarted by Supervisor when it finishes
 *
 * @todo Create an integration test for the queue
 * @todo Might be worth sending the proxy address in via an env var, and reading it here?
 */

use Proximate\Service\File;
use Proximate\Service\SiteFetcher as SiteFetcherService;
use Proximate\Queue\Read as QueueReader;

$root = realpath(__DIR__ . '/../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

if ($argc != 2)
{
    $command = __FILE__;
    die(
        sprintf("Syntax: %s <queue-path>\n", $command)
    );
}

$queuePath = $argv[1];
if (!file_exists($queuePath))
{
    die(
        sprintf("Error: the supplied queue path `%s` does not exist\n", $queuePath)
    );
}

$queue = new QueueReader($queuePath, new File());
$queue->
    setFetcher(new SiteFetcherService('proximate-proxy:8081'))->
    process();
