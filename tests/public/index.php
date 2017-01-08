<?php

/**
 * Front controller for testing purposes
 */

use Proximate\Queue\Write as Queue;
use Proximate\Service\File as FileService;
use Proximate\Test\RoutingTestHarness;

$root = realpath(__DIR__ . '/../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';
require_once $root . '/tests/bootstrap.php';

$app = new Slim\App();
$curlRecorder = new PestJSON('http://proximate-proxy:8081');
$curlPlayback = new PestJSON('http://proximate-proxy:8082');
$queue = new Queue('.', new FileService());

// Set up routing object
$routing = new RoutingTestHarness($app);
$routing->setRecorderCurl($curlRecorder);
$routing->setPlaybackCurl($curlPlayback);
$routing->setQueue($queue);
$routing->execute();

$app->run();

