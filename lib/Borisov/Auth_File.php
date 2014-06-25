<?php

// This class provides authentication framework via flatfile DB.
//
// Author: George Borisov <george@gir.me.uk>

namespace Borisov;

if (!file_exists(ROOT_PATH . Config::get('auth_file'))) {
	exit ("ERROR: auth file not found");
}

class Auth_File extends Auth_Base
{
	public static function changePassword($u, $p) {
		$a_return = [];

		$a = self::userExists($u);
		if ($a['code'] === 200) {
			$a_auth = self::load();
			$u = strtolower($u);
			$h = self::makeHash($p);
			
			$a_auth[$u]['hash'] = $h;
			$a_return = self::save($a_auth);

		} else {
			$a_return = $a;
		}

		return $a_return;
	}

	public static function newUser($u, $p, $is_admin=false) {
		$a_return = [];

		$a = self::userExists($u);
		if ($a['code'] === 200) {
			$a_return = ['code' => 400, 'error' => 'User already exists'];

		} elseif ($a['code'] === 404) {
			$a_auth = self::load();
			$u = strtolower($u);
			$h = self::makeHash($p);

			$a_auth[$u] = ['hash' => $h, 'is_admin' => 0];
			$a_return = self::save($a_auth);
		} else {
			$a_return = $a;
		}

		return $a_return;
	}

	protected static function getUser($user) {
		$a_auth = self::load();
		if (isset ($a_auth[$user])) {
			return $a_auth[$user];
			
		} else {
			return false;
		}
	}

	private static function load() {
		if ($a = file_get_contents(ROOT_PATH . Config::get('auth_file'))) {
			$a = json_decode($a, true);
		} else {
			$a = [];
		}
		return $a;
	}

	private static function save($a) {
		if (file_put_contents(ROOT_PATH . Config::get('auth_file'), json_encode($a, JSON_PRETTY_PRINT)) === false) {
			$a_return = ['code' => 500, 'error' => 'Unable to write to DB file'];

		} else {
			$a_return = ['code' => 200];
		}
		return $a_return;
	}
}
