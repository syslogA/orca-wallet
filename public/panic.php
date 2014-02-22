<?php
session_start();
define("IN_SCRIPT", true);
require_once("config.php");

function _getPanicStatusText($c ) {
	return (!$c) ? '<span class="error">FAILED</span>':'<span class="ok">DONE</span>';
} 

if ( defined("DEMO_MODE") && DEMO_MODE ) {
	die("<h2>Panic disabled on demo mode.</h2>");
}
if ( !isLoggedIn() ) {
	die("<h2>Login Required.</h2>");
}	


echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>'.SCRIPT_NAME.' '.SCRIPT_VER.' - PANIC MODE</title>
	<link href="favicon.ico" rel="icon" type="image/x-icon" />
	<style type="text/css">
	body {
		background-color: #000000;
		color: #FFFFFF;
	}
	.error {
		color: #D8000C;
		font-weight: bold;
	}
	.ok {
		color: #4F8A10;
		font-weight: bold;
	}
	table, th, td {
		border: 1px solid #D4E0EE;
		border-collapse: collapse;
		font-family: "Trebuchet MS", Arial, sans-serif;
		align: center;
	}

	td, th {
		padding: 4px;
	}
	</style>
	</head>
<body>';
$rData=array();
if ( !DB_GetRows( $rData, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>'1') ) ) {
	die("<h2>UNABLE TO LOAD RPC SERVERS</h2>");
}
echo '<center><h2 style="color:red">'.SCRIPT_NAME.' '.SCRIPT_VER.'<br />PANIC MODE</h2></center>
<table width="100%" border="1"><tr><th>ID</th><th>SERVER LINK</th><th>ACTION</th><th>RESULT</th></tr>';
$i=0;
foreach( $rData AS $srv ) {
	$srvLink="rcp://".$srv['username'].":***@".$srv['host'].":".$srv['port'];
	$pText="<tr><td>".($i+1)."</td><td>".$srvLink."</td><td>Sending stop command</td><td>";
	$c=rpc_request($rpcOut, $srv, array('method'=>'stop',  'params'=>array() ));
	$pText.=_getPanicStatusText( $c );
	$pText.="</td></tr>";
	
	$pText.="<tr><td>".($i+2)."</td><td>".$srvLink."</td><td>Removing record from database </td><td>";
	$c=DB_delete(DB_TABLE_RPC_SERVER, array('WHERE'=>"`id`='".$srv['id']."'"));
	$pText.=_getPanicStatusText( $c );
	$pText.="</td></tr>";
	$i+=2;
	echo $pText;
}
$pText="<tr><td>".($i+1)."</td><td>LOCAL DB SERVER</td><td>Removing database (".DB_NAME.")</td><td>";
$c=mysql_query("DROP DATABASE `".DB_NAME."`");
$pText.=_getPanicStatusText( $c );

echo $pText;

$pText="<tr><td>".($i+2)."</td><td>LOCAL DIR</td><td>Trying to Remove configuration file.</td><td>";
$c=@unlink("xconfig.php");
$pText.=_getPanicStatusText( $c );
echo $pText.='</td></tr>';
if ( !$c ) {
	$pText="<tr><td>".($i+2)."</td><td>LOCAL DIR</td><td>Setting permission to 0111 for configuration file.(failsafe for unlink)</td><td>";
	$c=chmod("config.php", 0111);
	$pText.=_getPanicStatusText( $c );
}

echo $pText.='</td></tr></table></body></html>';
?>
