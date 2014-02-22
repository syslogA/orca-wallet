<?php
if ( !defined("IN_SCRIPT") ) {
	error_log("Hack Attemtp: Direct access denied!", 0);
	die("<h2>Direct access not permitted<h2>");
}

function DB_SetOptions($rOptions ) {
	$rTmpOpt=array();
	if ( is_array($rOptions) && !empty($rOptions) ) {
		$rTmpOpt[] = ( array_key_exists("WHERE", $rOptions)  )? sprintf("WHERE %s", $rOptions['WHERE']): "";
		$rTmpOpt[] = ( array_key_exists("ORDERBY", $rOptions)  )? sprintf("ORDER BY %s", $rOptions['ORDERBY']): "";
		$rTmpOpt[] = ( array_key_exists("GROUPBY", $rOptions)  )? sprintf("GROUP BY %s", $rOptions['GROUPBY']): "";
		$rTmpOpt[] = ( array_key_exists("LIMIT", $rOptions)  )? sprintf("LIMIT %s", $rOptions['LIMIT']): "";
	}
	return implode(" ", $rTmpOpt);
}

function DB_GetRows(&$rOut, $strTable, $rStruct, $rOptions ) {
	$strQuery= sprintf("SELECT `%s` FROM `%s` %s;", implode("`, `", array_map('mysql_escape_string', array_values($rStruct) )), $strTable, DB_SetOptions( $rOptions ) );
	$resResult = mysql_query( $strQuery ); 
	if ( !$resResult )  return false;
	$n = @mysql_num_rows( $resResult);
	for($i=0; $i<$n; $i++ )
		$rOut[$i] = @mysql_fetch_array( $resResult, MYSQL_ASSOC );
	@mysql_free_result( $resResult );
	return true;
}

function DB_GetRow(&$rOut, $strTable, $rStruct,$rOptions ) {
	$rOptions[]=array('LIMIT'=>"0,1");
	$strQuery= sprintf("SELECT `%s` FROM `%s` %s;", implode("`, `", array_map('mysql_escape_string', array_values($rStruct) )), $strTable, DB_SetOptions( $rOptions ) );
	$resResult = mysql_query( $strQuery ); 
	if ( !$resResult )  return false;
	if ( mysql_num_rows( $resResult) != 1 ) return false;
	$rOut= @mysql_fetch_array( $resResult, MYSQL_ASSOC );
	@mysql_free_result( $resResult );
	return true;
}

function DB_Insert( $strTable, $rSchema ) {
	foreach( $rSchema AS &$tmpvalue )
		$tmpvalue=htmlspecialchars($tmpvalue);
	$query = "INSERT INTO `".$strTable."` (`".implode("`, `", array_map('mysql_escape_string', array_keys($rSchema)))."`) ";
	$query.= " VALUES ('".implode("', '", array_map('mysql_escape_string', $rSchema))."');";
	$result = @mysql_query( $query );
	return ( !$result )? false:  mysql_insert_id();
}

function DB_delete( $strTable, $rOptions) {
	$query="DELETE FROM `".$strTable."` ";
	if ( !empty( $rOptions) && is_array( $rOptions) )
		$query.=DB_SetOptions( $rOptions);
	$query.=";";
	$result = mysql_query( $query );
	return ( !$result )? false: mysql_affected_rows();
} 

function DB_update( $strTable, $rSchema, $rOptions ) {
	$query= "UPDATE `".$strTable."` SET ".implode(', ', array_map(function($k,$v){return "`$k` = '".mysql_real_escape_string($v)."'";}, array_keys($rSchema), array_values($rSchema)));
	if ( is_array( $rOptions) && !empty( $rOptions ) )  
		$query.=DB_SetOptions( $rOptions);
	$query.=";";
	$result = mysql_query( $query );
	return ( !$result ) ?  false:  mysql_affected_rows();
}

?>
