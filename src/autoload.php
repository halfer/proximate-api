<?php

/* 
 * Custom autoloader
 *
 * @todo Swap this to a Composer approach instead
 */

namespace Proximate;

class Autoloader
{
    const PREFIX = 'Proximate';
    const PREFIX_TEST = 'Proximate\\Test';

	public function mainLoader($class, $namespacePrefix, $relativeClassPath)
	{
		$loaded = false;
        $classPath = $this->getProjectRoot() . '/' . $relativeClassPath;
		$classSlashes = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $namespaceSlashes = str_replace('\\', DIRECTORY_SEPARATOR, $namespacePrefix);

        // This transforms, for example:
        //
        // Proximate\Test\QueueTestBase -> /my/classpath/QueueTestBase.php
		$path =
            $classPath .
			str_replace($namespaceSlashes . '/', '', $classSlashes) . '.php';

		if (file_exists($path))
		{
			require_once $path;
			$loaded = true;
		}

		return $loaded;
	}

    /**
	 * Is this class in our app namespace?
	 * 
	 * @param string $class
     * @param string $prefix
	 * @return boolean
	 */
	public function ourNamespace($class, $prefix)
	{
		return substr($class, 0, strlen($prefix)) == $prefix;
	}

    protected function getProjectRoot()
    {
        return realpath(__DIR__ . '/..');
    }
}

spl_autoload_register(
	function($class)
	{
		$loader = new \Proximate\Autoloader();
		if ($loader->ourNamespace($class, Autoloader::PREFIX))
		{
			$loader->mainLoader($class, Autoloader::PREFIX, 'src/');
		}
	}
);
