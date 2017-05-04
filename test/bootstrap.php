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
			$ok = $loader->mainLoader($class, Autoloader::PREFIX_TEST, 'test/unit/classes/');
            if (!$ok)
            {
                $loader->mainLoader($class, Autoloader::PREFIX_TEST, 'test/integration/classes/');
            }
		}
	}
);

// Check we have access to curl
if (!extension_loaded('curl'))
{
    die("Curl module required to run tests\n");
}

// Turn on any errors/warnings that could bork JSON output. This emulates the settings
// on Travis, so we don't find tests passing locally and failing remotely.
error_reporting(-1);
ini_set('display_errors', true);
