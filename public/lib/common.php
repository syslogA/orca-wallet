<?php
if ( !defined("IN_SCRIPT") ) {
	error_log("Hack Attemtp: Direct access denied!", 0);
	die("<h2>Direct access not permitted<h2>");
}
function FixJsonDecodeBug( $str ) {
	return ($str == "_empty_" )? "": $str;
}
function generateHash( $string ) {
	return hash_hmac('sha256', $string, SECRET_KEY, true);
}
function isIpv4($string) {
	return ip2long( $string );
}

function isHostname($str) {
	if ( preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $str) )
		if ( preg_match("/^.{1,253}$/", $str) )
			if ( preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $str) )
				return true;
	return false;
}
function isValidHost( $str) {
	return ( isHostname( $str ) || isIpv4( $str ) )? true: false;
}
function objectToArray ($object) {
	if(!is_object($object) && !is_array($object))
		return $object;
	return array_map('objectToArray', (array) $object);
}

function isWalletAddress( $strAddr ) {
	return true;
}

function parseSendManyList( &$strOut, $strText ) {
	$tmpList=explode(",", $strText);
	$tobj = new stdClass();
	if ( count( $tmpList ) < 1 )
		return FALSE;
	foreach( $tmpList AS $val ) {
		$tmp=explode(":", $val);
		if ( count($tmp) != 2 || !isWalletAddress( trim($tmp[0])) || floatval( $tmp[1]) <= 0 )
			return FALSE;
		$tmp[0]=trim($tmp[0]);
		$tobj->$tmp[0]=floatval(trim($tmp[1]));
	}
	$strOut=$tobj;
	return TRUE;
}

function isLoggedIn() {
	if ( empty( $_SESSION ) ) {
		return false;
	}
	else if ( empty( $_SESSION[SESSION_KEY] ) || empty( $_SESSION[SESSION_KEY]['iLogin']) ) {
		return false;
	}
	else if ( empty( $_SESSION[SESSION_KEY]['strKey']) ) {
		return false;
	}
	else if ( $_SESSION[SESSION_KEY]['strKey'] !== generateHash( $_SESSION[SESSION_KEY]['username'].$_SESSION[SESSION_KEY]['loginDate']) ) {
		return false;
	}
	return true;
}


function insertLog($status, $node, $msg, $extrainfo) {
	DB_Insert( 
		DB_TABLE_LOGS, 
		array(
			'id'=>'', 'ownerID'=>0, 'created'=>date("Y/m/d H:i:s"), 'node'=>$node,
			'message'=>$msg, 'extrainfo'=>$extrainfo, 'status'=>$status
		)
	);
}

function isBruteForce() {
	$rList=array();
	$where="`ip`='".$_SERVER['REMOTE_ADDR']."' ";
	$where.= ( empty( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) ? "": " OR `ip`='".$_SERVER["HTTP_X_FORWARDED_FOR"]."'";
	if ( !DB_GetRows( $rList, DB_TABLE_BRUTES, array('id'), array('WHERE'=>$where) ) )
		return true;
	if ( count( $rList ) >= MAX_BRUTE_COUNT )
		return true;
	return false;
}

?>
