<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) {
    session_start();
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");



class PHP
{
	var $LastErrorDescription = "";
	
	function __construct() 
	{
		$this->oDatabase = new Database();
		$this->DatabaseConnection = $this->oDatabase->GetConnection();

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/")) {

			mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
		
		}
	
	}
   

	public function getPhpVersions()
	{

		$directory = `ls /usr/bin/php*`;

		$directoryArray = explode("\n", $directory);

		$phpVersions = [];


		foreach ($directoryArray as $next) {
			$output = `$next -v`."<p>";
			$version = substr($output, strpos($output, " ") + 1);
			$version = substr($version, 0, strpos($version, " "));
			$version = substr($version, 0, strrpos($version, "."));

			$pos1 = strpos($version, ".");
			$pos2 = false;

			if (strlen($version) >= ($pos1 + 1)) {
				$pos2 = strpos($version, ".", $pos1 + 1);
			}

			if ($pos2 !== false) {
				$version = substr($version, 0, $pos2);
			}

			$version = trim($version);

			if ( (! in_array($version, $phpVersions)) && (strlen($version) > 0) ) {

				$phpVersions[] = $version;

			}

		}

		if (!empty($phpVersions)) {
			sort($phpVersions);
		}


		return $phpVersions;
		
	}
        
}

