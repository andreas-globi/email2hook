<?php

// sample config

// [{name:"name",domains:["domain.com"],url:"http://domain.com/hook",count:1},...]
// where
// - name    = a genric service name
// - domains = an array of email catch domains (can include * wildcard)
// - url     = the http end-point to post to
// - count   = number of daemons to run

$config = [
	
	[
		"name" => "app_a",
		"domains" => ["domain.tld"],
		"url" => "http://domain.tld/hook.php",
		"count" => 1
	],
	[
		"name" => "app_b",
		"domains" => ["*.domain2.tld", "domain3.tld"],
		"url" => "http://domain2.tld/hook/",
		"count" => 1
	],
	
];
