<?php

/**
 * A copy of the real front controller, except for changed queue/cache locations
 */

use Proximate\Routing\Routing;

$root = realpath(__DIR__ . '/../../../..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';
require_once $root . '/test/bootstrap.php';

class TestFrontController extends \Proximate\FrontController
{
    public function getRouting(\Slim\App $app)
    {
        return new Routing($app);
    }
}

$frontController = new TestFrontController(
    '/tmp/proximate-tests/queue',
    '/tmp/proximate-tests/cache-read'
);
$frontController->execute();
