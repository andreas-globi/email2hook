<?php
require_once __DIR__."/include.php";

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

while ( true ) {

	// real work will come here
	$filename = reserveFile($app_name);
	
	if ( $filename ) {
		
		$content = file_get_contents($filename);
		// try post it to the hook
		$ret = curlToHook($app['url'], $content, $filename);
		if ( $ret ) {
			// success - remove file
			@unlink($filename);
		} else {
			// failure - rename to end of queue
			$newname = pushRename($filename);
			rename($filename, $newname);
		}
		unReserve($filename);
	}
	
	usleep(100000);
	
}

