<?php

// general shared functions

// get global config
function config() {
	global $config;
	return $config;
}

// get list of pids for running process
function getPidList($program_name) {
	$foo = exec('ps -ax | grep "' . $program_name.'"', $output);
	if ( !$foo || !is_array($output) ) return [];
	$pids = [];
	foreach ( $output as $line ) {
		if ( stristr($line, "grep ") ) continue;
		$pids[] = explode(" ", trim($line))[0];
	}
	return $pids;
}

// return the mail dir
function maildir() {
	return "/home/".$GLOBALS['me']."/mail";
}

// get all files in an app's queue
function getQueue($app_name) {
	$directory = maildir()."/".$app_name."/new";
	$files = array_diff(scandir($directory), array('..', '.'));
	foreach ( $files as $k => $file ) {
		$files[$k] = $directory."/".$file;
	}
	return $files;
}

// clear screen
function clear() {
	passthru('clear');
}

// get a server-wide lock
function getLock() {
	$lockFile = "/tmp/email2hook.lock";
	while (true) {
		$fp = fopen($lockFile, "w");
		$lock = flock($fp, LOCK_EX);
		if ( $lock ) {
			return $fp;
		}
		fclose($fp);
	}
}

// release lock
function releaseLock($fp) {
	fclose($fp);
}

// reserve a mail file
function reserveFile($app_name) {
	$lock = getLock();
	if ( $lock === false ) return false;
	$queue = getQueue($app_name);
	foreach ( $queue as $filename ) {
		// if it's a lock file so just see if it's too old
		if ( substr($filename, -5) == ".lock" ) {
			$age = time() - filemtime($filename);
			if ( $age > 60 ) {
				@unlink($filename);
			}
			continue;
		}
		// if it's not a lock file check if there is a lock file
		if ( in_array($filename.".lock", $queue) ) {
			continue;
		}
		// if it's not a lock file and there is no lock file create one
		touch($filename.".lock");
		return $filename;
	}
	return false;
}

// remove lock file
function unReserve($filename) {
	@unlink($filename.".lock");
}

// print a table in ascii (like from a sql result)
function printTable ( $table ) {
	$i=0;
	$table = array_values($table);
	if (is_array($table) and sizeof($table)>0) {
		if (!is_array($table[0])) {
			$table = array($table);
		}
		$lengths = [];
		foreach ($table as $num => $row) {
			if ($i==0) {
				foreach ($row as $key => $value) {
					$lengths[$key] = max((isset($lengths[$key])?($lengths[$key]):(0)), strlen($key));
				}
			}
			$i++;
			foreach ($row as $key => $value) {
				if ( ! is_string($value) && ! is_numeric($value) ) $value = json_encode($value);
				$lengths[$key] = max((isset($lengths[$key])?($lengths[$key]):(0)), strlen($value));
			}
		}
		$i=0;
		foreach ($table as $num => $row) {
			if ($i==0) {
				foreach ($row as $key => $value) {
					echo "+-".str_pad("", $lengths[$key], "-")."-";
				}
				print "+\n";
				foreach ($row as $key => $value) {
					echo "| ".str_pad($key, $lengths[$key], " ")." ";
				}
				print "|\n";
				foreach ($row as $key => $value) {
					echo "+-".str_pad("", $lengths[$key], "-")."-";
				}
				print "+\n";
			}
			$i++;
			foreach ($row as $key => $value) {
				if ( ! is_string($value) && ! is_numeric($value) ) $value = json_encode($value);
				echo "| ".str_pad($value, $lengths[$key], " ")." ";
			}
			print "|\n";
			if ($i == sizeof($table)) {
				foreach ($row as $key => $value) {
					echo "+-".str_pad("", $lengths[$key], "-")."-";
				}
				print "+\n";
			}
		}
	} else {
		print "NULL\n";
	}
}
