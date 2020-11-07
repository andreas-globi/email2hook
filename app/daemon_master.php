<?php

// master daemon - spawns child daemons and ensures they're running

require_once __DIR__."/include.php";
error_log("starting master");

$time_this = filemtime(__FILE__);
$time_config = filemtime(__DIR__."/../config/config.php");

while ( true ) {

	clearstatcache();
	// if this file changed - quit and wait for re-spawn
	$check = filemtime(__FILE__);
	if ( $check != $time_this ) {
		error_log("killing master");
		exit;
	}
	// if config changed - quit and wait for re-spawn
	$check = filemtime(__DIR__."/../config/config.php");
	if ( $check != $time_config ) {
		error_log("killing master");
		exit;
	}
	
	foreach ( $config as $app ) {
		$need = $app['count'];
		$have = sizeof(getPidList("daemon_process.php ".$app['name']));
		while ( $have < $need ) {
			error_log("spawning for ".$app['name']);
			$cmd = 'nohup php ' . __DIR__."/daemon_process.php " . $app['name'] . ' >> /var/log/email2hook.log 2>&1 &';
			exec($cmd);
			$have++;
		}
	}
	
	sleep(1);
	
}

