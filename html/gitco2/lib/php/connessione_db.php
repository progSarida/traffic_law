<?php
   	$db_host = "";
   	$db_user = "root";
   	$db_password = "";
   	
   	$c = get_var('c');
   	
   	$db_database = "gitco2";
   	
   	error_reporting(E_ALL ^ E_DEPRECATED);
   	
   	mysql_connect( $db_host , $db_user , $db_password ) || die (mysql_error());
   	mysql_select_db($db_database) || die (mysql_error());   	
?>