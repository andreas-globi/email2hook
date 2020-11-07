<?php

require_once __DIR__."/app/include.php";

$queue = getQueue("globi");
$file = array_pop($queue);
print $file."\n";

$fp = getLock();
if ( $fp ) {
	$content = file_get_contents($file);
	print_r($content);
	sleep(10);
	releaseLock($fp);
}
