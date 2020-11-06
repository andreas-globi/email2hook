<?php
require_once __DIR__."/include.php";
ini_set("error_log", "/var/log/email2hook.log");
error_log("starting master");

$time = conf_time();

while ( true ) {

	// if config file changed - quit and wait for re-spawn
	$check = conf_time();
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

