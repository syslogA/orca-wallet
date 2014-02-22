<?php

$fnList=array(
	array('fn'=>'json_encode', 'libname'=>'php-json', 'package'=>'', 'installcmd'=>'sudo apt-get install php5-json'),
	array('fn'=>'ssh2_connect', 'libname'=>'php-ssh2', 'package'=>'', 'installcmd'=>'apt-get install libssh2-1-dev libssh2-php' ),
	array('fn'=>'mysql_connect', 'libname'=>'php-mysql', 'package'=>'', 'installcmd'=>''),
	array('fn'=>'curl_init', 'libname'=>'php-curl', 'package'=>'', 'installcmd'=>'')
);

foreach( $fnList AS $val ) {
	if ( function_exists($val['fn']) ) {
		echo ("<h2>You must install ".$val['libname']."</h2><br>you can use ".$val['installcmd']);
	}
}

/*
1- Install apache webserver  ( if you want to connect on internet don't configure apache with SSL 
2- Install required php librarys for ubuntu ( libssh2-1-dev, libssh2-php, 
*/
1- sudo apt-get install apache2
1.a- sudo a2enmod ssl  ( Enable apache2 SSL MODE )
1.b- sudo service apache2 restart 
1.c- sudo mkdir /etc/apache2/ssl 
1.d- sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt (and give required information)
1.e- 
