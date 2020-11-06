<?php

// get global config
function config() {
	global $config;
	return $config;
}

// get time of last config update
function conf_time() {
	clearstatcache();
	$conf = __DIR__."/../config/config.php";
	$time = filemtime($conf);
	return $time;
}

// get list of pids for running process
function getPidList($program_name) {
	$foo = exec('ps -ax | grep "' . $program_name.'"', $output);
	if ( !$foo || !is_array($output) ) return [];
	$pids = [];
	foreach ( $output as $line ) {
		if ( stristr($line, "grep ") ) continue;
		$pids[] = explode(" ", trim($line))[0];
	}
	return $pids;
}
