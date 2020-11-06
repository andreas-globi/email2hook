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

$time = conf_time();

while ( true ) {

	// if config file changed - quit and wait for re-spawn
	$check = conf_time();
	if ( $check != $time ) {
		error_log("killing process ".$app_name);
		exit;
	}
	
	// real work will come here
	
	usleep(100000);
	
}

