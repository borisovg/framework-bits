<?php

if (!defined('ROOT_PATH')) {
	define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));
}

final class Config
{
	private static $c	= [];
	private static $file	= 'config.php';
	private static $dir	= '/config/';

	public static function load($file = '') {
		if (!$file) {
			$file = self::$file;
		}
		$file = ROOT_PATH . self::$dir . $file;
		//var_dump($file);
		if (file_exists($file)) {
			if ($a = require $file) {
				self::$c = array_replace_recursive(self::$c, $a);
			}
			//var_dump(self::$c);
		}
	}

	public static function get($k) {
		if (isset(self::$c[$k])) {
			return self::$c[$k];
		} else {
			return FALSE;
		}
	}
}

Config::load();

// load any local configuration which would include:
// * anything not suitable for Github (like credentials)
// * configuration overrides, e.g. changing DB server to localhost
Config::load('local_config.php');

// debug logging
if (Config::get('debug')) {
	error_reporting(E_ALL);
}
