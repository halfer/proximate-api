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
}

// Currently uses these paths:
//
// ./queue for the queue
// ./cache for the proxy storage
$frontController = new TestFrontController($root . '/queue', $root . '/cache');
$frontController->execute();
