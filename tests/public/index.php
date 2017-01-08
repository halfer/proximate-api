<?php

/**
 * Front controller for testing purposes
 */

use Proximate\Test\RoutingTestHarness;

$root = realpath(__DIR__ . '/../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';
require_once $root . '/tests/bootstrap.php';

class TestFrontController extends \Proximate\FrontController
{
    public function getRouting(\Slim\App $app)
    {
        return new RoutingTestHarness($app);
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

$frontController = new TestFrontController('.');
$frontController->execute();
