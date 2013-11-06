<?php

if (!defined('ROOT_PATH')) {
	define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));
}

spl_autoload_register(
	function ($className) {
		$className = ltrim($className, '\\');
		// dirty hack to allow separate folders for namespaces
		// actually this seems to be the exact way PSR-0 does it :P -a.
		$className = str_replace('\\', '/', $className);

		$path = ROOT_PATH . "/lib/{$className}.php";

		if (file_exists($path)) {
			// Class found. Require it once.
			require_once $path;
		} else {
			// Class not found
			return; // Pass control to next autoloader
		}
	},
	true, // throw
	true // prepend
);
