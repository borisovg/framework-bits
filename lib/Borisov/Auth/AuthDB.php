<?php

// This class provides authentication framework via MySQL database
//
// Author: George Borisov <george@gir.me.uk>

namespace Borisov\Auth;

use \DB;

class AuthDB extends Base
{
	protected static $db = [];

	public static function changePassword($u, $p) {
		$a_return = [];

		$a = self::userExists($u);
		if ($a['code'] === 200) {
			$db = self::getDB('rw');
			$u = strtolower($u);
			$h = self::makeHash($p);
			$db->q("UPDATE auth SET hash='$h' WHERE user='$u'");
			$a_return = ['code' => 200];
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
			$db = self::getDB('rw');
			$u = strtolower($u);
			$h = self::makeHash($p);
			
			if ($is_admin) {
				$sql = "INSERT INTO auth (user, hash, is_admin) VALUES ('$u', '$h', 1)";
			} else {
				$sql = "INSERT INTO auth (user, hash) VALUES ('$u', '$h')";
			}
			$db->q($sql);
			$a_return = ['code' => 200];
		} else {
			$a_return = $a;
		}

		return $a_return;
	}

	protected static function getDB($type = 'ro') {
		if (!isset (self::$db[$type])) {
			self::$db[$type] = new DB($type);
		}
		return self::$db[$type];
	}

	protected static function getUser($user) {
		if (self::usernameIsLegal($user)) {
			$db = self::getDB();
			return $db->get_row("SELECT hash, is_admin FROM auth WHERE user='{$user}'");
		}
	}
}
