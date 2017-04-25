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

// Supply queue and cache folder locations
//
// @todo Add in the restrictions on symlinks placed by Flysystem
$frontController = new TestFrontController(
    $root . '/queue',
    $root . '/../proximate-requester/cache/data'
);
$frontController->execute();
