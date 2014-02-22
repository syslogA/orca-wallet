<?php

session_start();
define("IN_SCRIPT", true);
require_once("config.php");

if ( !isLoggedIn() ) {
	header("Location: logout.php");
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo sprintf( "%s v: %s", SCRIPT_NAME, SCRIPT_VER);?></title>
	<link href="favicon.ico" rel="icon" type="image/x-icon" />
</head>
<body> 
    <div id="loading-mask" style=""></div> 
    <div id="loading"> 
        <div id="loading-ind" class="loading-indicator">
        	<?php echo sprintf( "%s v: %s", SCRIPT_NAME, SCRIPT_VER);?> &#8482;<br>
            <img id="loading-image" src="images/ajax_indicator.gif" width="32" height="32" style="margin-left:8px; margin-right:8px;float:left;vertical-align:top;"/><br>
            <span id="loading-msg">Loading styles...</span>
        </div> 
    </div>
    <link rel="stylesheet" type="text/css" href="css/apploader.css" />
    <link rel="stylesheet" type="text/css" href="js/ext4/resources/ext-theme-gray/ext-theme-gray-all.css" /> 
    <link rel="stylesheet" type="text/css" href="js/ext4/ux/grid/css/GridFilters.css" />
    <link rel="stylesheet" type="text/css" href="js/ext4/ux/grid/css/RangeMenu.css" />
    
    
    <!--<link rel="stylesheet" type="text/css" href="js/ext4/resources/css/ext-all.css">-->
    <script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Loading UI...';</script> 
    <script type="text/javascript" src="js/ext4/ext-all-debug-w-comments.js"></script>
    <script type="text/javascript" src="js/types.js"></script>
    <script type="text/javascript" src="js/common.js"></script>
	<script type="text/javascript" src="js/app.js"></script>
	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Loading depencies...';</script> 
    <script type="text/javascript">document.getElementById('loading-msg').innerHTML = 'Loading application...';</script>
    <script type="text/javascript">
		var loading=document.getElementById("loading");
		if(loading)document.body.removeChild(loading);
		var mask=document.getElementById("loading-mask");
		if(mask)document.body.removeChild(mask);
    </script>
</body>
</html>
