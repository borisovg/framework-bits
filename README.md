# Assorted framework bits and pieces

This repository contains various bits of code that I use as part of web development.
I don't expect these to be used by anyone else, but don't let that stop you! ;-)

## Autoloader

inc/autoload.php

This will load classes from lib/ directory.
Namespaces are supported: \Namespace\Class will be loaded from lib/Namespace/Class.php.

## Configuration module

lib/Config.php

This class will read configuration files from the config/ directory (config.php and local\_config.php by default).

null Config::load('fileName') - merge configuration from file<br>
string Config::get('key') - get a configuration item, or false if item not found

## MySQL wrapper module

lib/DB.php

Extends 'mysqli' class.
Uses the Config class to read database configuration and create a connection.
Provides a number of additional helper methods to simplify working with MySQL responses.

DB::\_\_construct(string $type = 'ro') - the type parameter defines with set of credentials to use (e.g: ['mysql']['auth']['ro'])

## Authentication module

lib/Auth.php - 'frontent' class, extends the 'database' class, allows for modularity of database layer<br>
lib/Auth\_DB.php - 'database' class (MySQL), extends 'base' class, uses the Config and DB classes<br>
lib/Auth\_Base.php - 'base' class

Passwords are stored as individually-salted SHA256 hashes.

The MySQL version expects the following table to exist:
 
	CREATE TABLE IF NOT EXISTS `auth` (
	  `user` varchar(64) NOT NULL,
	  `hash` varchar(64) NOT NULL,
	  `is_admin` tinyint(1) DEFAULT NULL,
	  PRIMARY KEY (`user`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

array Auth::changePassword(string $username, string $password) - change password<br>
array Auth::login(string $username, string $password, bool $reloadPage = true) - attempts to login and optionally reloads the page<br>
array Auth::newUser(string $username, string $password, bool $isAdmin) - add a new user<br>
bool Auth::isAdmin() - check if user is an administrator<br>
bool Auth::isAuthenticated() - check if session is authenticated<br>
bool Auth::usernameIsLegal(string $username) - check if username is in acceptable format (regex: /^[\\w\\d\\.\\-\\@]+$/)<br>
null Auth::logout() - logs out user and reloads page<br>
null Auth::destroySession() - similar to logout() but does not reload the page<br>
string Auth:getUserName() - returns the username of the currently logged in user (or false if not logged in)
