<?php

/* 
 * Custom autoloader
 */

namespace Proximate;

class Autoloader
{
    const PREFIX = 'Proximate';

	public function mainLoader($class)
	{
		$loaded = false;
		$slashes = str_replace('\\', '/', $class);
		$path =
			$this->getProjectRoot() . '/src/' .
			str_replace(self::PREFIX . '/', '', $slashes) . '.php';
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
	 * @return boolean
	 */
	public function ourNamespace($class)
	{
		return substr($class, 0, strlen(self::PREFIX)) == self::PREFIX;
	}
}

spl_autoload_register(
	function($class)
	{
		$loader = new \Proximate\Autoloader();
		if ($loader->ourNamespace($class))
		{
			$loader->mainLoader($class);
		}
	}
);