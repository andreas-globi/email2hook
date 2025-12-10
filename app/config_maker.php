<?php

// creates config files for postfix from php config array

require_once __DIR__."/include.php";

$domains = [];
$mailboxes = [];

foreach ( $config as $app ) {
	
	if ( ! is_array($app) || ! isset($app['name'])  || ! isset($app['url'])  || ! isset($app['domains'])  || ! isset($app['count']) ) {
		throw new Exception("bad config missing keys");
	}
	if ( ! is_string($app['name']) || ! is_string($app['url']) || ! is_numeric($app['count']) || ! is_array($app['domains']) ) {
		throw new Exception("bad config bad values");
	}
	$name = $app['name'];
	if ( preg_replace("/[^a-zA-Z0-9_]/", "", $name) != $name ) {
		throw new Exception("bad name ".$name);
	}
	
	// vdomains wants /((\w[\w\-]*)\.)+domain\.tld/ OK
	// vmailbox wants /@((\w[\w\-]*)\.)+domain\.tld/ appname/
	
	foreach ( $app['domains'] as $domain ) {
		
		if ( ! is_string($domain) || empty($domain) ) {
			throw new Exception("bad domain ".$domain);
		}
		
		$domain = preg_quote($domain);
		$domain = str_replace(['\*\.', '\*'], ['((\w[\w\-]*)\.)+', '(\w[\w\-]*)+'], $domain);
		$domains[] = "/".$domain."/ OK";
		$mailboxes[] = "/@".$domain."/ " . $app['name']."/";
		
	}
	
}

// Write to temporary files first
$tmpVdomains = tempnam(sys_get_temp_dir(), 'vdomains');
$tmpVmailbox = tempnam(sys_get_temp_dir(), 'vmailbox');

file_put_contents($tmpVdomains, implode("\n", $domains)."\n");
file_put_contents($tmpVmailbox, implode("\n", $mailboxes)."\n");

// Move to /etc/postfix with sudo
exec("sudo mv " . escapeshellarg($tmpVdomains) . " /etc/postfix/vdomains");
exec("sudo chown root:postfix /etc/postfix/vdomains");
exec("sudo chmod 644 /etc/postfix/vdomains");

exec("sudo mv " . escapeshellarg($tmpVmailbox) . " /etc/postfix/vmailbox");
exec("sudo chown root:postfix /etc/postfix/vmailbox");
exec("sudo chmod 644 /etc/postfix/vmailbox");
