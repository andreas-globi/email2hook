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
		$row["err"] = 0;
		$row["old"] = 0;
		$row["graph"] = "";
		if ( sizeof($queue) > 0 ) {
			$row["age"] = 0;
			foreach ( $queue as $file ) {
				$time = filectime($file);
				$age = getAge($file);
				if ( substr(basename($file), 0, 4) == "xerr" ) {
					$row["err"]++;
					if ( $age > $row["old"] ) {
						$row["old"] = $age;
					}
				} else {
					if ( $age > $row["age"] ) {
						$row["age"] = $age;
					}
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
