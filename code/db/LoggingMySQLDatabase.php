<?php

/**
 * Description of LoggingMySQLDatabase
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class LoggingMySQLDatabase extends MySQLDatabase {
	
	public static $enabled = false;
	
	protected $logs = array();
	
	public function startLog($name) {
		$this->logs[$name] = array();
	}
	
	public function getLog($name) {
		if (isset($this->logs[$name])) {
			return $this->logs[$name];
		}
	}
	
	public function endLog($name) {
		$log = $this->getLog($name);
		unset($this->logs[$name]);
	}
	
	public function query($sql, $errorLevel = E_USER_ERROR) {
		$starttime = microtime(true);
		$handle = mysql_query($sql, $this->dbConn);
		
		$endtime = round(microtime(true) - $starttime,4);
		
		if (self::$enabled) {
			foreach ($this->logs as $name => $log) {
				$this->logs[$name][] = array('time' => $endtime, 'sql' => $sql, 'from' => $this->findQuerySource());
			}
		}

		if(!$handle && $errorLevel) $this->databaseError("Couldn't run query: $sql | " . mysql_error($this->dbConn), $errorLevel);
		return new MySQLQuery($this, $handle);
	}
	
	protected function findQuerySource() {
		$backtrace = debug_backtrace();
		if (count($backtrace) > 9) {
			$ret = array_slice($backtrace, 3, 7);
		} else {
			$ret = array_slice($backtrace, 0, 5);
		}

		return $ret;
	}

	public function logALog($name) {
		$log = $this->getLog($name);
		if ($log) {
			$logFile = TEMP_FOLDER . '/query_log_'.$name;

			$out = "\n\nBEGIN===========================================\n\n";
			$total = 0;
			foreach ($log as $entry) {
				$break = false;
				foreach (self::$ignore as $blacklist) {
					if (strpos($entry['sql'], $blacklist) !== false) {
						$break = true;
					}
				}
				if ($break) {
					continue;
				}
				$total += $entry['time'];
				$out .= $entry['time'] . ' - ' . $entry['sql'] . "\n";
				foreach ($entry['from'] as $point) {
					$line = isset($point['line']) ? $point['line'] : '';
					$class = isset($point['class']) ? $point['class'] : '';
					$function = isset($point['function']) ? $point['function'] : '';
					$file = isset($point['file']) ? $point['file'] : '';

					$out .= "-- #$line $class::$function() $file\n";
				}

				$out .= "\n-------------------------------------\n\n";
			}

			$out .= "\n\n=========================================\n";
			$req = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
			$out .= count($log) . " queries in a time of $total to $req\n";
			$out .= "=========================================\n\n";
			file_put_contents($logFile, $out, FILE_APPEND);
		}
	}
}

