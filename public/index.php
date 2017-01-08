<?php

use Proximate\Queue\Write as Queue;
use Proximate\Service\File as FileService;

$root = realpath(__DIR__ . '/..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

$app = new Slim\App();
$curlRecorder = new PestJSON('http://proximate-proxy:8081');
$curlPlayback = new PestJSON('http://proximate-proxy:8082');
$queue = new Queue('/var/proximate/queue', new FileService());

// Set up routing object
$routing = new \Proximate\Routing\Routing($app);
$routing->setRecorderCurl($curlRecorder);
$routing->setPlaybackCurl($curlPlayback);
$routing->setQueue($queue);
$routing->execute();

$app->run();
