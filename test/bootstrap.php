<?php

$root = realpath(__DIR__ . '/..');
require_once $root . '/vendor/autoload.php';
require_once $root . '/src/autoload.php';

use \Proximate\Autoloader;

spl_autoload_register(
	function($class)
	{
		$loader = new Autoloader();
		if ($loader->ourNamespace($class, Autoloader::PREFIX_TEST))
		{
			$loader->mainLoader($class, Autoloader::PREFIX_TEST, 'test/classes/');
		}
	}
);

// Check we have access to curl
if (!extension_loaded('curl'))
{
    die("Curl module required to run tests\n");
}
