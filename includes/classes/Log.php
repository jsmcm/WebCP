<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

class Log
{
	static private $_instance = null;
	
	private function __contruct()
	{
		// making it private for sinigleton use...
	}

	// A method to get our singleton instance
	public static function getInstance()
	{
		if (!(self::$_instance instanceof Log)) 
		{
			self::$_instance = new Log();
		}
	
		return self::$_instance;
	}



	function WriteLog($Severity, $Message)
	{
		// only allow logging if you are specifically checking for a problem


		$Severity = strtoupper($Severity);

		if(!is_dir($_SERVER["DOCUMENT_ROOT"]."/../logs")) {

			mkdir($_SERVER["DOCUMENT_ROOT"]."/../logs");
			chmod($_SERVER["DOCUMENT_ROOT"]."/../logs", 0755);
		}

		$RunLog = $_SERVER["DOCUMENT_ROOT"]."/../logs/runlog";
		
		
		$CriticalLog = $_SERVER["DOCUMENT_ROOT"]."/../logs/criticallog";

		$WriteLog = true;

		if($Severity == "DEBUG") {
			// This log level should NEVER be turned on in production systems, it exposes user name and passwords in the log files
			//$WriteLog = false;
		}

		if($WriteLog == true) {

			$FileHandle = fopen($RunLog, 'a');	
			fwrite($FileHandle, date("Y-m-d H:i:s")." - ".$Severity." - ".$Message."\r\n");
			fclose($FileHandle);

			if(strtolower($Severity) == "critical") {
				$FileHandle = fopen($CriticalLog, 'a');	
				fwrite($FileHandle, date("Y-m-d H:i:s")." - ".$Severity." - ".$Message."\r\n");
				fclose($FileHandle);
			}
		}

		if(file_exists($RunLog)) {
			chmod($RunLog, 0755);
			chown($RunLog, "www-data");
			chgrp($RunLog, "www-data");
		}

		if(file_exists($CriticalLog)) {
			chmod($CriticalLog, 0755);
			chown($CriticalLog, "www-data");
			chgrp($CriticalLog, "www-data");
		}
	}

} 
