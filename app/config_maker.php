<?php

require_once __DIR__."/include.php";

// creates config files for postfix from php config array

$domains = [];
$mailboxes = [];

foreach ( $config as $app ) {
	
	if ( ! is_array($app) || ! isset($app['name'])  || ! isset($app['url'])  || ! isset($app['domains'])  || ! isset($app['count']) ) {
		throw new Exception("bad config missing keys");
	}
	if ( ! is_string($app['name']) || ! is_string($app['url']) || ! is_numeric($app['count']) || ! is_array($app['domains']) ) {
		throw new Exception("bad config bad values");
	}
	
	// vdomains wants /domain\.tld/ OK
	// vmailbox wants /@domain\.tld/ appname/
	
	foreach ( $app['domains'] as $domain ) {
		
		if ( ! is_string($domain) || empty($domain) ) {
			throw new Exception("bad domain ".$domain);
		}
		
		$domain = preg_quote($domain);
		$domain = str_replace(['\*\.', '\*'], ['*.', '*'], $domain);
		$domains[] = "/".$domain."/ OK";
		$mailboxes[] = "/@".$domain."/ " . $app['name'];
		
	}
	
}

file_put_contents(__DIR__."/../config/vdomains", implode("\n", $domains)."\n");
file_put_contents(__DIR__."/../config/vmailbox", implode("\n", $mailboxes)."\n");
