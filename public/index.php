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

/*
 * Supply queue and cache folder locations
 *
 * I've found that Flysystem seems to prefer its base directory being used only for its
 * own purposes, hence I've used a two-level structure: "cache" is the base of the Flysystem
 * and "data" is a subfolder. If the base of the Flysystem contains symlinks an error
 * "Links are not supported" may be encountered - the two-level structure avoids that.
 */
$frontController = new TestFrontController(
    $root . '/queue',
    $root . '/cache/data'
);
$frontController->execute();
