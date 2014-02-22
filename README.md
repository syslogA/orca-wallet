walletshare
===========

LAMP based multi orginal bitcoin rpcserver web control interface.

requires
apache-server, mysql-server, php, php-mysql, php-curl, php-ssh2, php-json

external or internal ssh server for authentication

How to setup;
1- change config-dist.php to config.php
2- execute sql/*.sql ( it will create database, and import db sturcture  )
2- open config.php make changes.

define("SESSION_KEY", "wwallet"); //php session storage key.
define("SECRET_KEY", "ALFANUMERIC_CHARS_ONLY"); //some secret text for generating hashs.
define("DB_HOST", "localhost"); //mysql database server hostname
define("DB_USER", "ANY_MYSQL_USER");  //mysql database user
define("DB_PASS", "ANY_MYSQL_PASS"); //mysql database password
define("DB_NAME", "swallet"); //mysql database name ( if you edit this you have to make change in sql/*.sql )
define("S_TIMEZONE", "Europe/Istanbul"); // your timezone.

define("SSH2_AUTH_HOST", "localhost"); // authentication ssh server
define("SSH2_AUTH_PORT", 22); // authentication server port 
define("MAX_BRUTE_COUNT", 3); // brute force protection count.
define("DEMO_MODE", false); // set debug mode false or true

$allowUserNameList=array('root', 'someotheruser'); //empty array causes all user can login 
$allowIPv4List=array("127.0.0.1", "10.0.2.2"); // empty array causes allows all ip addresses

