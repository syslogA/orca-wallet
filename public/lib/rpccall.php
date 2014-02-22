<?php
if ( !defined("IN_SCRIPT") ) {
	error_log("Hack Attemtp: Direct access denied!", 0);
	die("<h2>Direct access not permitted<h2>");
}

function getHttpResponseByCode($ihttpCode) {
	$ResponseList=array(
		100=>"Continue", 101=>"Switching Protocols", 200=>"OK", 201=>"Created",
		202=>"Accepted", 203=>"Non-Authoritative Information", 204=>"No Content",
		205=>"Reset Content", 206=>"Partial Content", 300=>"Multiple Choices",
		301=>"Moved Permanently", 302=>"Found", 303=>"See Other",
		304=>"Not Modified", 305=>"Use Proxy", 306=>"(Unused)", 307=>"Temporary Redirect",
		400=>"Bad Request", 401=>"Unauthorized", 402=>"Payment Required", 403=>"Forbidden",
		404=>"Not Found", 405=>"Method Not Allowed", 406=>"Not Acceptable", 
		407=>"Proxy Authentication Required", 408=>"Request Timeout", 409=>"Conflict",
		410=>"Gone", 411=>"Length Required", 412=>"Precondition Failed", 413=>"Request Entity Too Large",
		414=>"Request-URI Too Long", 415=>"Unsupported Media Type", 416=>"Requested Range Not Satisfiable",
		417=>"Expectation Failed", 500=>"Internal Server Error", 501=>"Not Implemented", 502=>"Bad Gateway",
		503=>"Service Unavailable", 504=>"Gateway Timeout", 505=>"HTTP Version Not Supported"
	);
	if ( array_key_exists($ihttpCode, $ResponseList) )
		return $ResponseList[$ihttpCode];
	return "Unknown";
}

function isResponseValid( $ihttpCode) {
	$validList=array(200, 302);
	return ( in_array( $ihttpCode, $validList) )?true: false;
}

function rpc_request(&$rOut, $srvinfo, $rPostDATA) {
	$sURL=sprintf("%s://%s:%s/", ( $srvinfo['useSSL'])?	"https": "http", $srvinfo['host'],$srvinfo['port']);
	$sPWD=sprintf("%s:%s", $srvinfo['username'], $srvinfo['password']);
	$requestID= "deneme";
	$sendJson=array('jsonrpc'=>'1.0', 'id'=>$requestID, 'method'=>'', 'params'=>array());
	$sendJson['method']=$rPostDATA['method'];
	$sendJson['params']=$rPostDATA['params'];
	$curl = curl_init();
	if ( !empty( $srvinfo['useSSL']) ) {
		if ( $srvinfo['useSSL']&4 )
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		else  
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		
		if ( ($srvinfo['useSSL']&8 ) && is_readable( $srvinfo['ca_path']) ) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($curl, CURLOPT_CAINFO,  $srvinfo['ca_path']);
		}
		else {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		}
	}
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content type:application/json"));
	curl_setopt($curl, CURLOPT_USERAGENT, "Opera/9.80 (X11; Linux x86_64) Presto/2.12.388 Version/12.16");
	curl_setopt($curl, CURLOPT_URL, $sURL);
	curl_setopt($curl, CURLOPT_USERPWD, $sPWD);
	curl_setopt($curl, CURLOPT_POST, TRUE);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode( $sendJson) );
	$response = curl_exec($curl);
	$curlinfo = curl_getinfo($curl);
	if($response === false) {
		$rOut=(object)array('error'=>(object)array('code'=>curl_errno($curl), 'message'=>curl_error($curl)));
	}
	//else if ( !isResponseValid($curlinfo['http_code']) ) {
		//$rOut=(object)array('error'=>(object)array('code'=>$curlinfo['http_code'], 'message'=>"Remote server said: ".getHttpResponseByCode($curlinfo['http_code'])));
	//}
	else {
		$rOut=json_decode( $response );
	}
	return ( $rOut->error !== NULL && is_object( $rOut->error) ) ? FALSE:TRUE;
}






?>
