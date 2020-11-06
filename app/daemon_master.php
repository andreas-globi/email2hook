<?php

// master daemon - spawns child daemons and ensures they're running

require_once __DIR__."/include.php";
ini_set("error_log", "/var/log/email2hook.log");
error_log("starting master");

$time = filemtime(__FILE__);

while ( true ) {

	// if this file changed - quit and wait for re-spawn
	clearstatcache();
	$check = filemtime(__FILE__);
	if ( $check != $time ) {
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

