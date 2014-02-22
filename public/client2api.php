<?php
session_start();
define("IN_SCRIPT", true);
require_once("config.php");



$requestList=array(
	'Auth', 'AddRpcServer', 'TerminateRpcServerByID', 'getnewaddress',
	'sendtoaddress', 'sendmany', 'move', 'setaccount',
	'FlushBrutes', 'FlushLogs'
);

$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Bad Request', 'reason'=>'Your browser sent a request that this server could not understand.'));

if ( empty($_POST['action']) || !in_array( $_POST['action'], $requestList) ) die(json_encode( $jsonData));
$currentAction=$_POST['action'];
$rUserInfo=array();


if ( $currentAction == "Auth")  {
	$ssh2_conn;
	$bruteData=array();
	if ( empty( $_POST['username']) || strlen( $_POST['username'] ) > 32 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Failed!', 'reason'=>'Username can not be blank and max 32 characters long.'));
	else if ( !empty( $allowUserNameList) && !in_array($_POST['username'], $allowUserNameList) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Failed!', 'reason'=>'username denied!'));
	else if ( empty( $_POST['password']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Failed!', 'reason'=>'Password can not be blank.'));
	else if ( !empty( $allowIPv4List) && !in_array($_SERVER['REMOTE_ADDR'], $allowIPv4List) && !in_array($_SERVER["HTTP_X_FORWARDED_FOR"], $allowIPv4List))
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Failed!', 'reason'=>$_SERVER['REMOTE_ADDR'].' is not allowed to login.'));
	else if ( isBruteForce() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Denied!', 'reason'=>'Too many login attempts'));
	else if ( !($ssh2_conn = @ssh2_connect(SSH2_AUTH_HOST, SSH2_AUTH_PORT) ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Server Error!', 'reason'=>'Can not connect to authentication server.'));
	else if ( !@ssh2_auth_password ( $ssh2_conn, $_POST['username'], $_POST['password']) ) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Failed!', 'reason'=>'Authentication failed invalid username or password.'));
		DB_Insert(DB_TABLE_BRUTES, array('id'=>'', 'created'=>date("Y-m-d H:i:s"), 'ip'=>$_SERVER['REMOTE_ADDR'], 'logindata'=>'user={'.$_POST['username'].'}  password={'.$_POST['password'].'}'));
		insertLog("ERROR", "Login", "Login Failure!", "Incorrect Login user={".$_POST['username']."}  password={".$_POST['password']."} host={".$_SERVER['REMOTE_ADDR']."}");
	}
	else {
		$jsonData=array('success'=>true, 'root'=>array(), 'error'=>array());
		insertLog("OK", "Login", "user ".$_POST['username']." logged in ", "logindate: ".date("Y-m-d H:i:s"));
		$_SESSION[SESSION_KEY]=array(
			'iLogin'=>true,
			'loginDate'=>date("Y-m-d H:i:s"),
			'username'=>$_POST['username']
		);
		$_SESSION[SESSION_KEY]['strKey'] = generateHash( $_POST['username'].$_SESSION[SESSION_KEY]['loginDate']);
	}
}
else if ( $currentAction == "AddRpcServer")  {
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( defined("DEMO_MODE") && DEMO_MODE )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Demo Mode Enabled', 'reason'=>'Change is not allowed in demo mode!'));
	else if ( empty( $_POST['rpc_host']) || !isValidHost( $_POST['rpc_host']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Unable to Add Server!', 'reason'=>'Invalid hostname or ipv4 address.'));
	else if ( empty( $_POST['rpc_port']) || intval( $_POST['rpc_port'] ) < 1 || intval( $_POST['rpc_port']) > 65535 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Unable to Add Server!', 'reason'=>'Invalid Port Number.'));
	else if ( empty( $_POST['rpc_user']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Unable to Add Server!', 'reason'=>'Invalid username.'));
	else if ( empty( $_POST['rpc_pass']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Unable to Add Server!', 'reason'=>'Invalid password.'));
	else if ( !empty($_POST['rpc_ssl_enabled']) && !empty( $_POST['rpc_ssl_options']) && in_array(8, $_POST['rpc_ssl_options']) && !is_readable( $_POST['rpc_peer_cainfo_path']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Unable to Add Server!', 'reason'=>'unable to access local certificate.!'));
	else {
		$RpcSrv=array(
			'id'=>'', 
			'host'=>$_POST['rpc_host'],
			'port'=>intval( $_POST['rpc_port']),
			'useSSL'=>0,
			'username'=>$_POST['rpc_user'],
			'password'=>$_POST['rpc_pass'],
			'created'=>date("Y-m-d H:i:s"),
			'title'=>( !empty($_POST['rpc_title']) ) ? $_POST['rpc_title']: "Untitled",
			'icon'=>( !empty($_POST['rpc_icon']) && file_exists(DIR_COIN_ICONS.$_POST['rpc_icon']) )?$_POST['rpc_icon'] : "default.png",
			'ca_path'=>'',
			'backup_path'=>( !empty($_POST['rpc_backup_path']) ) ? $_POST['rpc_backup_path']: ""
			
		);
		if ( !empty($_POST['rpc_ssl_enabled']) ) {
			foreach( $_POST['rpc_ssl_options'] AS $tmp )
				$RpcSrv['useSSL']=$RpcSrv['useSSL']|intval($tmp);
			$RpcSrv['ca_path'] = ( empty( $_POST['rpc_peer_cainfo_path']) ) ? "": $_POST['rpc_peer_cainfo_path'];
		}
		
		if ( !empty( $_POST['rpc_verify']) && !rpc_request($rpcOut, $RpcSrv, array('method'=>'getinfo', 'params'=>array()))) {
			$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to load wallet info!<br />Reason:'.$rpcOut->error->message));
			insertLog("ERROR", "getinfo", "unable to add new rpc server", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
		}
		else if ( !DB_Insert( DB_TABLE_RPC_SERVER, $RpcSrv) )
			$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Unable to add Server!', 'reason'=>'System error.'.mysql_error()));
		else 
			$jsonData=array('success'=>true, 'root'=>array(), 'error'=>array(), 'reason'=>'');
	}
}
else if ( $currentAction=="TerminateRpcServerByID") {
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( defined("DEMO_MODE") && DEMO_MODE )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Demo Mode Enabled', 'reason'=>'Change is not allowed in demo mode!'));
	else if ( empty( $_POST['ID']) || !is_numeric( $_POST['ID']) || intval( $_POST['ID']) < 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Failed!', 'reason'=>'A general system error occurred invalid ID'));
	else {
		if ( !DB_delete( DB_TABLE_RPC_SERVER, array('WHERE'=>"`id`='".intval( $_POST['ID'])."'")) )
			$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Failed!', 'reason'=>'A general system error occurred SQL QUERY FAILED.'));
		else
			$jsonData=array('success'=>true, 'root'=>array(), 'error'=>array());
	}
}
else if ( $currentAction == "getnewaddress") {
	$srv=array();
	$accountList=array();
	$rpcOut;
	if ( empty($_POST['label']) ) $_POST['label'] = "UNTITLED";
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( defined("DEMO_MODE") && DEMO_MODE )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Demo Mode Enabled', 'reason'=>'Change is not allowed in demo mode!'));
	else if ( empty( $_POST['serverID']) || intval( $_POST['serverID'])< 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval($_POST['serverID'])."'" ) ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to get server information.'));
	else if ( !rpc_request($rpcOut, $srv, array('method'=>'getnewaddress', 'params'=>array($_POST['label'])))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'unable to get new address! For details check logs.'));
		insertLog("ERROR", "getnewaddress", "unable to get new address!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$jsonData=array('success'=>true, 'root'=>array(), 'error'=>array());
		insertLog("OK", "getnewaddress", "new address created!", sprintf("address:%s", $rpcOut->result));
	}
}
else if ( $currentAction == "sendtoaddress") {
	$srv=array();
	$rpcOut;
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( defined("DEMO_MODE") && DEMO_MODE )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Demo Mode Enabled', 'reason'=>'Change is not allowed in demo mode!'));
	else if ( empty( $_POST['serverID']) || intval( $_POST['serverID'])< 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval($_POST['serverID'])."'" ) ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to get server information.'));
	else if (  empty( $_POST['sendTo']) || !isWalletAddress( $_POST['sendTo']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'You entered invalid wallet address.'));
	else if ( empty( $_POST['sendAmount']) || floatval( $_POST['sendAmount']) <= 0 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Send amount is invalid.'));
	else if (!rpc_request($rpcOut, $srv, array('method'=>'sendtoaddress', 'params'=>array($_POST['sendTo'], floatval( $_POST['sendAmount']), '', '')))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>$rpcOut->error->message));
		insertLog("ERROR", "sendtoaddress", "unable to send coins!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$jsonData=array('success'=>true, 'root'=>array(), 'error'=>array());
		insertLog("OK", "sendtoaddress", "successfully send!", sprintf("TXID:%s", $rpcOut->result));
	}
}
else if ( $currentAction == "sendmany"){//ben bitmedim rcp call hazır değil.FixJsonDecodeBug unutma
	$srv=array();
	$rpcOut;
	$strRcptList="";
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( defined("DEMO_MODE") && DEMO_MODE )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Demo Mode Enabled', 'reason'=>'Change is not allowed in demo mode!'));
	else if ( empty( $_POST['serverID']) || intval( $_POST['serverID'])< 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID.'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval($_POST['serverID'])."'" ) ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to get server information.'));
	else if (  empty( $_POST['sendManyAccount']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'You entered invalid wallet address.'));
	else if ( empty( $_POST['sendManyList']) || false == parseSendManyList( $strRcptList, $_POST['sendManyList']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Your list invalid, please make sure your list is valid.'));
	else if (!rpc_request($rpcOut, $srv, array('method'=>'sendmany', 'params'=>array( FixJsonDecodeBug($_POST['sendManyAccount']), $strRcptList, 0, date("Y-M-d H:i:s")  )))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>$rpcOut->error->message));
		insertLog("ERROR", "sendmany", "sendmany call failed!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>$_POST['sendManyList']));
		insertLog("OK", "sendmany", "sendmany call succeed!", "List: ".$_POST['sendManyList']);
	}
}
else if ( $currentAction == "move"){
	$srv=array();
	$rpcOut;
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( defined("DEMO_MODE") && DEMO_MODE )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Demo Mode Enabled', 'reason'=>'Change is not allowed in demo mode!'));
	else if ( empty( $_POST['serverID']) || intval( $_POST['serverID'])< 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval($_POST['serverID'])."'" ) ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to get server information.'));
	else if (  empty( $_POST['MoveSourceAccount']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Source account empty, for fixing this problem you should put a label on address.'));
	else if (  empty( $_POST['MoveDestAccount']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Destination account empty, for fixing this problem you should put a label on address.'));
	else if (  empty( $_POST['MoveAmount']) || floatval( $_POST['MoveAmount']) <=0 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'AMount must be bigger than zero'));
	else if (!rpc_request($rpcOut, $srv, array('method'=>'move', 'params'=>
		array(
			FixJsonDecodeBug($_POST['MoveSourceAccount']),
			FixJsonDecodeBug($_POST['MoveDestAccount']), 
			floatval( $_POST['MoveAmount']),
			1,
			"move date: ".date("Y M d H:i")
		)))) {
			$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>$rpcOut->error->message));
			insertLog("ERROR", "move", "unable to move!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$jsonData=array('success'=>true, 'root'=>array(), 'error'=>array());
		insertLog("OK", "sendtoaddress", "successfully moved!", serialize($rpcOut->result));
	}
}
else if ( $currentAction == "setaccount"){
	$srv=array();
	$rpcOut;
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( defined("DEMO_MODE") && DEMO_MODE )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Demo Mode Enabled', 'reason'=>'Change is not allowed in demo mode!'));
	else if ( empty( $_POST['serverID']) || intval( $_POST['serverID'])< 1 )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to load server ID'));
	else if ( !DB_GetRow( $srv, DB_TABLE_RPC_SERVER, $rDbStruct[DB_TABLE_RPC_SERVER], array( 'WHERE'=>"`id`='".intval($_POST['serverID'])."'" ) ) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to get server information.'));
	else if (  empty( $_POST['account']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Label name cannot be blank.'));
	else if (  empty( $_POST['address']) )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Wallet address cannot be empty.'));
	else if (!rpc_request($rpcOut, $srv, array('method'=>'setaccount', 'params'=> array( $_POST['address'], FixJsonDecodeBug($_POST['account']) )))) {
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>$rpcOut->error->message));
		insertLog("ERROR", "setaccount", "unable to set account!", sprintf("code:#%d,message:%s", $rpcOut->error->code, $rpcOut->error->message));
	}
	else {
		$jsonData=array('success'=>true, 'root'=>array(), 'error'=>array());
		insertLog("OK", "setaccount", "successfully renamed!", serialize($rpcOut->result));
	}
}
else if ( $currentAction == "FlushBrutes"){
	$rpcOut;
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( defined("DEMO_MODE") && DEMO_MODE )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Demo Mode Enabled', 'reason'=>'Change is not allowed in demo mode!'));
	else if ( !DB_delete(DB_TABLE_BRUTES, array('WHERE'=>'1')))
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to flush brute table.'));
	else {
		$jsonData=array('success'=>true, 'root'=>array(), 'error'=>array());
		insertLog("OK", "FlushBrutes", "successfully flushed!", "");
	}
}
else if ( $currentAction == "FlushLogs"){
	$rpcOut;
	if ( !isLoggedIn() )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Login Required', 'reason'=>'You have to login for this operation.'));
	else if ( defined("DEMO_MODE") && DEMO_MODE )
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Demo Mode Enabled', 'reason'=>'Change is not allowed in demo mode!'));
	else if ( !DB_delete(DB_TABLE_LOGS, array('WHERE'=>'1')))
		$jsonData=array('success'=>false, 'root'=>array(), 'error'=>array('title'=>'Error!', 'reason'=>'Unable to flush log table.'));
	else {
		$jsonData=array('success'=>true, 'root'=>array(), 'error'=>array());
		insertLog("OK", "FlushLogs", "log records successfully flushed!", "");
	}
}
header("content type: application/json");
echo json_encode($jsonData);
?>
