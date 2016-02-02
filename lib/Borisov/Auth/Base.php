<?php

// This class provides base authentication framework
//
// Author: George Borisov <george@gir.me.uk>

namespace Borisov\Auth;

use \Borisov\Config;

if (!session_id()) {
    session_start();
}

abstract class Base
{
	// LSB Methods //

	public static function changePassword($u, $p) {
		exit ('ERROR: method not implemented');
	}

	public static function newUser($u, $p, $is_admin=false) {
		exit ('ERROR: method not implemented');
	}
	
	protected static function getUser($user) {
		exit ('ERROR: method not implemented');
	}

	// Public Methods //

	public static function isAuthenticated($saveURI = true) {
		if (static::sessionExists()) {
			if (static::sessionIsExpired($_SESSION['expiry'])) {
				static::expireSession($saveURI);

			} else {
				static::updateSessionExpiry(Config::get('auth_session_timeout'));
				return TRUE;
			}
		}
		return FALSE;
	}

	public static function login($user, $pw, $reload = true) {
		$a_user = static::getUser($user);
		$success = false;
		if (!$a_user) {
			static::destroySession();
		} else {
			if (static::passwordIsCorrect($pw, $a_user['hash'])) {
				$success = $user;
				if (isset ($_SESSION['relogon'])) {
					unset ($_SESSION['relogon']);
				} else {
					static::destroySession();
					session_start();
				}
				$_SESSION['userName'] = $user;
				static::updateSessionExpiry(Config::get('auth_session_timeout'));
				if ($a_user['is_admin']) {
					$_SESSION['is_admin'] = TRUE;
				}
			} else {
				if (isset ($_SESSION['relogon'])) {
					if ($_SESSION['relogon']) {
						--$_SESSION['relogon'];
					} else {
						static::destroySession();
						// TODO: blacklist IP
					}
				} else {
					static::destroySession();
				}
			}
		}
		if ($reload) {
			if (isset ($_SESSION['lastURI'])) {
				header("Location: {$_SESSION['lastURI']}");
			} else {
				header("Location: {$_SERVER['REQUEST_URI']}");
			}
			exit;
		} else {
			return $success;
		}
	}

	public static function isAdmin() {
		if (static::sessionExists()) {
			if (static::sessionIsExpired($_SESSION['expiry'])) {
				static::expireSession();
				return FALSE;
			} else {
				if (isset ($_SESSION['is_admin'])) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	public static function logout() {
		if (isset ($_SESSION['lastURI'])) {
			header("Location: {$_SESSION['lastURI']}");
		} else {
			header('Location: /');
		}
		static::destroySession();
		exit;
	}


	public static function destroySession() {
		session_unset();
		session_destroy();
	}

	public static function getUserName() {
		if (static::isAuthenticated()) {
			return $_SESSION['userName'];
		} else {
			return false;
		}
	}

	public static function usernameIsLegal($u) {
		if (preg_match('/^[\w\d\.\-\@]+$/', $u)) {
			return true;
		} else {
			return false;
		}
	}

	public static function userExists($u) {
		$a_return = [];

		if (static::usernameIsLegal($u)) {
			if (static::getUser($u)) {
				$a_return = ['code' => 200];
			} else {
				$a_return = ['code' => 404, 'error' => 'User does not exist'];
			}
		} else {
			static::destroySession();
			$a_return = ['code' => 400, 'error' => 'Illegal username'];
		}

		return $a_return;
	}

	// Private Methods //

	protected static function sessionExists() {
		if (isset($_SESSION['userName'])) {
			return TRUE;
		}
		return FALSE;
	}

	protected static function sessionIsExpired($expiry_time) {
		if ($expiry_time > time()) {
			return FALSE;
		}
		return TRUE;
	}

	protected static function expireSession($saveURI) {
		$max_login_retries = 3;
		
		$_SESSION['relogon'] = $max_login_retries;

		// some request URIs should not be saved, e.g. API calls
		if ($saveURI) {
			$_SESSION['lastURI'] = $_SERVER['REQUEST_URI'];
		}
			
		if (!empty($_POST)) {
			$_SESSION['post_data'] = array();
			foreach ($_POST as $key=>$value) {
				$_SESSION['post_data'][$key] = $value;
			}
		}
		unset ($_SESSION['userName']);
	}

	protected static function updateSessionExpiry($session_timeout) {
		$_SESSION['expiry'] = time() + $session_timeout;
	}

	protected static function passwordIsCorrect($pw, $hash) {
		$a_hash = explode('$', $hash);
		array_pop($a_hash);
		$salt = implode('$', $a_hash);
		if (crypt($pw, $salt) === $hash) {
			return TRUE;
		}
		return FALSE;
	}

	protected static function makeHash($p) {
		$s = static::makeSalt();
		return crypt($p, $s);
	}

	protected static function makeSalt() {
		return '$5$'.bin2hex(mcrypt_create_iv(16));
	}

	// restore post data after session expiry
	protected static function restorePOST(&$a_vars, &$a_submits) {
		if (isset ($_SESSION['post_data'])) {
			foreach ($a_vars as $key=>$value) {
				if (isset ($_SESSION['post_data'][$key])) {
					$_POST[$key] = $_SESSION['post_data'][$key];
					unset ($_SESSION['post_data'][$key]);
				}
			}
			foreach ($a_submits as $key) {
				if (isset ($_SESSION['post_data'][$key])) {
					$_POST[$key] = $_SESSION['post_data'][$key];
					unset ($_SESSION['post_data'][$key]);
				}
			}
			 unset ($_SESSION['post_data']);
		} 
	}
}
