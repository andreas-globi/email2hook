<?php
require_once __DIR__."/include.php";
ini_set("error_log", "/var/log/email2hook.log");

$app_name = $argv[1];
if ( empty($app_name) ) {
	throw new Exception("missing argv name");
}
$app = false;
foreach ( $config as $x ) {
	if ( $x['name'] == $app_name ) {
		$app = $x;
	}
}
if ( $app === false ) {
	throw new Exception("invalid app ".$app_name);
}

error_log("starting process ".$app_name);

while ( true ) {

	// real work will come here
	$filename = reserveFile($app_name);
	if ( $filename ) {
		// do something
		error_log("processing ".$filename);
		sleep(1);
		unReserve($filename);
	}
	
	usleep(100000);
	
}

