<?php
if ( !defined("IN_SCRIPT") ) {
	error_log("Hack Attemtp: Direct access denied", 0);
	die("<h2>Direct access not permitted<h2>");
}
define("SESSION_KEY", "wwallet");
define("SECRET_KEY", "ALFANUMERIC_CHARS_ONLY");
define("DB_HOST", "localhost");
define("DB_USER", "MYSQL_DB_USERNAME");
define("DB_PASS", "MYSQL_DB_USER_PASSWORD");
define("DB_NAME", "swallet");
define("S_TIMEZONE", "Europe/Istanbul");

define("SSH2_AUTH_HOST", "localhost");
define("SSH2_AUTH_PORT", 22);
define("MAX_BRUTE_COUNT", 3);
define("DEMO_MODE", false);


define("SCRIPT_NAME", "Orca Rpc Server Control Interface");
define("SCRIPT_VER", "0.0.1 Beta");

define("DIR_SCRIPT", dirname(realpath(__FILE__)));
define("DIR_LIB", DIR_SCRIPT."/lib/");
define("DIR_COIN_ICONS", DIR_SCRIPT."/images/icons/cryptocurrency/");


define("DB_TABLE_RPC_SERVER", "rpc_server");
define("DB_TABLE_LOGS", "logs");
define("DB_TABLE_BRUTES", "brutes");

$allowUserNameList=array('root');
$allowIPv4List=array("127.0.0.1", "10.0.2.2");

$rDbStruct=array(  
	DB_TABLE_RPC_SERVER=>array(
		'id', 'host', 'port', 'useSSL', 'username', 'password', 'created', 'title', 'icon', 'ca_path', 'backup_path'
	),
	DB_TABLE_LOGS=>array('id', 'ownerID', 'created', 'node', 'message', 'extrainfo', 'status'),
	DB_TABLE_BRUTES=>array('id', 'created', 'ip', 'logindata')
);


$DepfnList=array(
	array('fn'=>'mysql_connect', 'lib'=>'php-mysql' ),
	array('fn'=>'json_decode', 'lib'=>'php-json' ),
	array('fn'=>'ssh2_connect', 'lib'=>'php-ssh2 ( libssh2-1-dev libssh2-php )' ),
	array('fn'=>'curl_init', 'lib'=>'php-curl' )
	
);

foreach( $DepfnList AS $tmp ) {
	if ( !function_exists( $tmp['fn'] ) )
		die("<h2>Dependencies check failed. You must install ".$tmp['lib']."</h2>");
}
unset( $tmp );


$dbLink;
$dbSelect;
if ( !($dbLink=mysql_connect(DB_HOST, DB_USER, DB_PASS)) )
	die("<h2>DB Server connection failed.</h2>");
if ( !($dbSelect=mysql_select_db(DB_NAME, $dbLink)) )
	die("<h2>DB Selection failed!</h2>");
@mysql_query("SET NAMES 'utf8'");
@date_default_timezone_set(S_TIMEZONE);

require_once(DIR_LIB."db.php");
require_once(DIR_LIB."common.php");
require_once(DIR_LIB."rpccall.php");
?>
