<?php

require_once __DIR__."/app/include.php";

$queue = getQueue("globi");
$file = array_pop($queue);
print $file."\n";

$raw = file_get_contents($file);
$raw = "hello \"there\"\n<world>\n(á, é, í, ó, ú, ñ, ¿)?\n";
//curlToHook("https://globi.requestcatcher.com/", $raw);
curlToHook("http://do-dev18.globi.ca/catch.php", $raw);

//for ( $i=0; $i<20; $i++) {
//	$file = pushRename($file);
//	print $file."\n";
//	sleep(1);
//}
exit();


//

/*
 * getLock()
 * getQueue()
 * find first file without corresponding .lock file
 * create .lock file
 * releaseLock()
 */
