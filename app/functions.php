<?php

// general shared functions


// get global config
// --------------------
function config() {
	global $config;
	return $config;
}


// get list of pids for running process
// --------------------
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
// --------------------
function maildir() {
	return "/home/".$GLOBALS['me']."/mail";
}


// get all files in an app's queue
// --------------------
function getQueue($app_name) {
	$directory = maildir()."/".$app_name."/new";
	$files = array_diff(scandir($directory), array('..', '.'));
	foreach ( $files as $k => $file ) {
		$files[$k] = $directory."/".$file;
	}
	return $files;
}


// rename mailbox file to xerr+time() . err_count . original
// --------------------
function pushRename($filename) {
	$name = basename($filename);
	$dir = dirname($filename);
	if ( substr($name, 0, 4) != "xerr" ) {
		return $dir."/xerr".time().".1.".$name;
	}
	// it's already an xerr.n.file
	$parts = explode(".", $name);
	$num = intval($parts[1]);
	unset($parts[0]);
	unset($parts[1]);
	$name = implode(".", $parts);
	$num++;
	return $dir."/xerr".time().".".$num.".".$name;
}


// get age from filename
// --------------------
function getAge($filename) {
	$name = basename($filename);
	$now = time();
	$parts = explode(".", $name);
	$time = 0;
	if ( substr($name, 0, 4) == "xerr" ) {
		$time = intval($parts[2]);
	} else {
		$time = intval($parts[0]);
	}
	if ( $time == 0 ) {
		$time = filectime($filename);
	}
	$age = $now - $time;
	return $age;
}


// curl post payload
// --------------------
function curlToHook($url, $payload, $filename="unknown") {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,15);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/raw'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	
	$result = curl_exec($ch);
	$success = true;
	if ( curl_errno($ch) ) {
		$success = false;
		error_log("ERROR posting file ".$filename." to ".$url);
		error_log(curl_error($ch));
	}
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ( $success && ( $http_status < 200 || $http_status > 209 ) ) {
		$success = false;
		error_log("ERROR HTTP Returned Code " . $http_status . " posting file ".$filename." to ".$url);
		$result = trim(strip_tags($result));
		if ( ! empty($result) ) {
			error_log($result);
		}
	}
	curl_close ($ch);
	return $success;
}


// clear screen
// --------------------
function clear() {
	passthru('clear');
}


// get a server-wide lock
// --------------------
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
// --------------------
function releaseLock($fp) {
	fclose($fp);
}


// reserve a mail file
// --------------------
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
// --------------------
function unReserve($filename) {
	@unlink($filename.".lock");
}


// print a table in ascii (like from a sql result)
// --------------------
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
