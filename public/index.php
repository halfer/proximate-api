<?php

use Proximate\Routing\Routing;

$root = realpath(__DIR__ . '/..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

class TestFrontController extends \Proximate\FrontController
{
    public function getRouting(\Slim\App $app)
    {
        return new Routing($app);
    }

    public function getRecorderCurl()
    {
        return new PestJSON('http://proximate-proxy:8081');
    }

    public function getPlaybackCurl()
    {
        return new PestJSON('http://proximate-proxy:8082');
    }
}

$frontController = new TestFrontController('/var/proximate/queue');
$frontController->execute();
