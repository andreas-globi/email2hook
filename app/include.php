<?php

// error log path
$GLOBALS['me'] = posix_getpwuid(fileowner(__FILE__))['name'];
ini_set("error_log", "/home/".$GLOBALS['me']."/email2hook.log");

// include requirements

require_once __DIR__."/../config/config.php";
require_once __DIR__."/functions.php";

if ( ! isset($config) || ! is_array($config) ) {
	throw new Exception("bad config");
}

