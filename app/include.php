<?php

require_once __DIR__."/../config/config.php";

if ( ! isset($config) || ! is_array($config) ) {
	throw new Exception("bad config");
}

