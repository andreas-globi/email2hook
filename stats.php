<?php

require_once __DIR__."/app/include.php";

while (true) {
	stats();
	sleep(1);
}

function stats() {
	$table = [];
	$config = config();
	foreach ( $config as $app ) {
		$name = $app['name'];
		$queue = getQueue($name);
		$row = [];
		$row["name"] = $name;
		$row["cnt"] = sizeof($queue);
		$row["age"] = 0;
		$row["graph"] = "";
		if ( sizeof($queue) > 0 ) {
			$row["age"] = 0;
			$now = time();
			foreach ( $queue as $file ) {
				$time = filectime($file);
				$age = $now - $time;
				if ( $age > $row["age"] ) {
					$row["age"] = $age;
				}
			}
			$size = intval($row['cnt']/10)+1;
			if ( $size > 30 ) $size = 30;
			$row['graph'] = str_pad("", $size, "*");
		}
		$table[] = $row;
	}
	clear();
	print "email hook queue stats\n";
	printTable($table);
	print "^C to quit\n";
}
