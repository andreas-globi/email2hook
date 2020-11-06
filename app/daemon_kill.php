<?php

// kills all process daemons

require_once __DIR__."/include.php";

$pids = getPidList("daemon_process.php");
foreach ($pids as $pid) {
	exec("kill ".$pid);
}

