<?php
session_start();
define("IN_SCRIPT", true);
require_once("config.php");
@session_unset();
@session_destroy();
if ( !empty( $_SESSION[SESSION_KEY] ) )
	unset( $_SESSION[SESSION_KEY]);
header("Location: index.php");
?>
