<?php
session_start();
define("IN_SCRIPT", true);
require_once("config.php");

$requestList=array(
	'logs', 'ListRpcServers', 'listreceivedbyaddress', 'p2p', 'transactionlist', 'gettransaction', 'getinfo',
	'getbalance', 'getCoinIcons', 'listaccounts', 'getbrutes', 'getaccountaddress', 'getsrvtotal', 'backup',
	'getaddressesbyaccount'
);
$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Bad Request', 'reason'=>'Your browser sent a request that this server could not understand.'));
if ( empty($_GET['action']) || !in_array( $_GET['action'], $requestList) ) 
	die( json_encode( $jsonData));
$currentAction=$_GET['action'];

if ( $currentAction == "void") {
	$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Void Request!', 'reason'=>'Ooups, this should not happen. contact with devs.'));
}
else if ( $currentAction == "logs") {
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( !DB_GetRows( $rData, DB_TABLE_LOGS, $rDbStruct[DB_TABLE_LOGS], array( 'WHERE'=>'1', 'LIMIT'=>'0, 20', 'ORDERBY'=>'`id` DESC') ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'System Error!', 'reason'=>'Unable to get server information.'));
	else {
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("last: %d logs. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "ListRpcServers") {
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( !DB_GetRows( $rData, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>'1') ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'System Error!', 'reason'=>'Unable to get server information.'));
	else {
		foreach( $rData AS &$val ) 
			$val['useSSL']=($val['useSSL'] )?'<img src="images/icons/secure.png" />': '<img src="images/icons/notsecure.png" />';
		unset( $val );
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d server found. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "listreceivedbyaddress" ) {
	$srv=array();
	$rpcOut=NULL;
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( empty( $_GET['serverID']) || intval( $_GET['serverID']) < 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval( $_GET['serverID'])."'") ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else if ( !rpc_request($rpcOut, $srv, array('method'=>'listreceivedbyaddress', 'params'=>array(0, true)))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to load address list!<br />Reason: '.$rpcOut->error->message ));
		insertLog("ERROR", "listreceivedbyaddress", "unable to get account list!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$n=count( $rpcOut->result );
		for ( $i=0; $i<$n; $i++ )
			$rData[]=(array)$rpcOut->result[$i];
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d address found. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "transactionlist" ) {
	$srv=array();
	$rpcOut=NULL;
	$rData=array();
	/* toparla beni */
	$account=( !empty( $_GET['account']) )? $_GET['account']:"*";
	$params= (!empty($_GET['limited']) ) ? array($account, 12):array($account, 1000, 0);
	
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( empty( $_GET['serverID']) || intval( $_GET['serverID']) < 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval( $_GET['serverID'])."'") ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else if ( !rpc_request($rpcOut, $srv, array('method'=>'listtransactions',  'params'=>$params ))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to load listtransactions!<br />Reason:'. $rpcOut->error->message ));
		insertLog("ERROR", "listtransactions", "unable to get transaction list!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$n=count( $rpcOut->result );
		for ( $i=0; $i<$n; $i++ ) {
			if ( !empty( $rpcOut->result[$i]->time) )
				$rpcOut->result[$i]->time = date("d M Y", $rpcOut->result[$i]->time);
			if ( !empty( $rpcOut->result[$i]->timereceived) )
				$rpcOut->result[$i]->timereceived = date("d-M-Y", $rpcOut->result[$i]->timereceived);
			
			
			$rData[]=(array)$rpcOut->result[$i];
		}
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d transaction(s) found. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "gettransaction" ) {
	$srv=array();
	$rpcOut=NULL;
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( empty( $_GET['serverID']) || intval( $_GET['serverID']) < 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( empty( $_GET['txID']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load transaction ID.'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval( $_GET['serverID'])."'") ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else if ( !rpc_request($rpcOut, $srv, array('method'=>'gettransaction',  'params'=>array($_GET['txID']) ))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to load transaction info!<br>Reason:'. $rpcOut->error->message));
		insertLog("ERROR", "gettransaction", "unable to get transaction info!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$rpcOut=objectToArray( $rpcOut );
		
		$rData=$rpcOut['result']['details'];
		$troot=$rpcOut['result'];
		unset( $troot['details']);
		$jsonData=array('success'=>true, 'root'=>$rData, 'troot'=>$troot, 'message'=>sprintf("Transaction details OK"), 'error'=>array());
	}
}
else if ( $currentAction == "p2p" ) {
	$srv=array();
	$rpcOut=NULL;
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( empty( $_GET['serverID']) || intval( $_GET['serverID']) < 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval( $_GET['serverID'])."'") ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else if ( !rpc_request($rpcOut, $srv, array('method'=>'getpeerinfo', 'params'=>array()))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to load peer info!<br />Reason:'.$rpcOut->error->message));
		insertLog("ERROR", "getpeerinfo", "unable to get peer info!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$n=count( $rpcOut->result );
		for ( $i=0; $i<$n; $i++ )
			$rData[]=(array)$rpcOut->result[$i];
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d peer(s) found. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "getbalance" ) {
	$srv=array();
	$rpcOut=NULL;
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( empty( $_GET['serverID']) || intval( $_GET['serverID']) < 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval( $_GET['serverID'])."'") ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else if ( !rpc_request($rpcOut, $srv, array('method'=>'getbalance', 'params'=>array()))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to load balance info!<br />Reason:'.$rpcOut->error->message));
		insertLog("ERROR", "getinfo", "unable to get balance info!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$rData=$rpcOut->result;
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d info(s) found. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "getinfo" ) {
	$srv=array();
	$rpcOut=NULL;
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( empty( $_GET['serverID']) || intval( $_GET['serverID']) < 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval( $_GET['serverID'])."'") ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else if ( !rpc_request($rpcOut, $srv, array('method'=>'getinfo', 'params'=>array()))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to load wallet info!<br />Reason:'.$rpcOut->error->message));
		insertLog("ERROR", "getinfo", "unable to get wallet info!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$tmpK=( (array)$rpcOut->result );
		$keys=array_keys( $tmpK );
		$values=array_values( $tmpK );
		$n=count( $tmpK );
		for( $i=0; $i<$n; $i++ ) {
			if ( $keys[$i] !== "errors" )
				$rData[] = array('key'=>$keys[$i], 'value'=>$values[$i]);
		}
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d info(s) found. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "listaccounts" ) {
	$srv=array();
	$rpcOut=NULL;
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( empty( $_GET['serverID']) || intval( $_GET['serverID']) < 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval( $_GET['serverID'])."'") ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else if ( !rpc_request($rpcOut, $srv, array('method'=>'listaccounts', 'params'=>array()))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to list accounts!<br />Reason:'.$rpcOut->error->message));
		insertLog("ERROR", "getinfo", "unable to list accounts!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$tmpK=( (array)$rpcOut->result );
		$keys=array_keys( $tmpK );
		$values=array_values( $tmpK );
		$n=count( $tmpK );
		for( $i=0; $i<$n; $i++ ) {
			if ( $keys[$i] !== "errors" )
				$rData[] = array('account'=>$keys[$i], 'balance'=>$values[$i]);
			
		}
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d account(s) found. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "getaccountaddress" ) {
	$srv=array();
	$rpcOut=NULL;
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( empty( $_GET['serverID']) || intval( $_GET['serverID']) < 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !isset($_GET['account']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load account'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval( $_GET['serverID'])."'") ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else if ( !rpc_request($rpcOut, $srv, array('method'=>'getaccountaddress', 'params'=>array(FixJsonDecodeBug( $_GET['account']) )))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to list accounts!<br />Reason:'.$rpcOut->error->message));
		insertLog("ERROR", "getaccountaddress", "unable to get account address!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$rData=array('account'=>$_GET['account'], 'address'=>$rpcOut->result);
		$jsonData=array('success'=>true, 'data'=>$rData, 'error'=>array());
	}
}
else if ( $currentAction == "getaddressesbyaccount" ) {
	$srv=array();
	$rpcOut=NULL;
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( empty( $_GET['serverID']) || intval( $_GET['serverID']) < 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !isset($_GET['account']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load account'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval( $_GET['serverID'])."'") ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else if ( !rpc_request($rpcOut, $srv, array('method'=>'getaddressesbyaccount', 'params'=>array(FixJsonDecodeBug( $_GET['account']) )))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to list accounts!<br />Reason:'.$rpcOut->error->message));
		insertLog("ERROR", "getaddressesbyaccount", "unable to get account address!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$n=count( $rpcOut->result );
		for ( $i=0; $i<$n; $i++ )
			$rData[]=array('address'=>$rpcOut->result[$i]);
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d address(es) found. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "getbrutes" ) {
	$rData=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( !DB_GetRows( $rData, DB_TABLE_BRUTES, $rDbStruct[DB_TABLE_BRUTES], array( 'WHERE'=>'1') ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'System Error!', 'reason'=>'Unable to get brutes.'));
	else {
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d record found. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "getCoinIcons" ) {
	$tmpIconList=glob(DIR_COIN_ICONS.'*.png');
	$rData=array();
	foreach( $tmpIconList as $tmpicon ) {
		$rData[] = array(
			'key'=>str_replace(DIR_COIN_ICONS, "", $tmpicon),
			'value'=>strtolower(str_replace(".png", "", str_replace(DIR_COIN_ICONS, "", $tmpicon)))
		);
	}
	$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d icon found. ", count($rData)), 'error'=>array());
}
else if ( $currentAction == "getsrvtotal" ) {
	$srvList=array();
	$rData=array();
	$rpcOut=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( !DB_GetRows($srvList, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array('WHERE'=>"1")) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else {
		foreach( $srvList AS $srv ) {
			if ( !rpc_request($rpcOut, $srv, array('method'=>'getbalance', 'params'=>array("*"))))
				continue;
			$rData[]=array('server'=>$srv['host'].':'.$srv['port'], 'title'=>$srv['title'], 'balance'=>$rpcOut->result );
		}
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d record found. ", count($rData)), 'error'=>array());
	}
}
else if ( $currentAction == "backup" ) {
	$srvList=array();
	$rData=array();
	$rpcOut=array();
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( !DB_GetRows($srvList, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array('WHERE'=>"1")) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server information.'));
	else {
		foreach( $srvList AS $srv ) {
			$backupFileName=$srv['backup_path'].date("Y_M_d_H_i_s").".dat";
			if ( empty( $srv['backup_path']) )
				$tmp=array( 'title'=>$srv['title'], 'server'=>$srv['host'].':'.$srv['port'], 'backupfilepath'=>"---", 'status'=>'ERROR: Backup Destination is not set. Backup Disabled' );
			else if ( !rpc_request($rpcOut, $srv, array('method'=>'backupwallet', 'params'=>array($backupFileName))))
				$tmp=array( 'title'=>$srv['title'], 'server'=>$srv['host'].':'.$srv['port'], 'backupfilepath'=>$backupFileName, 'status'=>'ERROR: '.$rpcOut->error->message );
			else 
				$tmp=array( 'title'=>$srv['title'], 'server'=>$srv['host'].':'.$srv['port'], 'backupfilepath'=>$backupFileName, 'status'=>'DONE');
			$rData[]=$tmp;
		}
		$jsonData=array('success'=>true, 'root'=>$rData, 'message'=>sprintf("Total: %d", count($rData)), 'error'=>array());
	}
}

header("content type: application/json");
echo @json_encode($jsonData);
?>

