<?php

// This is a simple templating class

namespace Borisov;

class Template
{
	private static $data = [];
	private static $templates = [];

	public static function get ($k) {
		return (isset (self::$data[$k])) ? self::$data[$k] : '';
	}

	public static function set ($k, $v) {
		self::$data[$k] = $v;
	}

	public static function css ($a = false) {
		if ($a) {
			self::set('css', $a);
		
		} else {
			$h = '';
			if ($css = self::get('css')) {
				foreach ($css as $a) {
					if (isset ($a[1])) {
						$h .= "<link rel='stylesheet' href='{$a[0]}' media='{$a[1]}'>";
					} else {
						$h .= "<link rel='stylesheet' href='{$a[0]}'>";
					}
				}
			}
			return $h;
		}
	}

	public static function js ($a = false) {
		if ($a) {
			self::set('css', $a);
		
		} else {
			$h = '';
			if ($js = self::get('js')) {
				foreach ($js as $file) {
					$h .= "<script src='$file'></script>";
				}
			}
			return $h;
		}
	}

	public static function register ($a) {
		foreach ($a as $t) {
			self::$templates[] = $t;
		}
	}

	public static function render () {
		foreach (self::$templates as $t) {
			require ROOT_PATH . "/templates/{$t}.php";
		}
	}
}
