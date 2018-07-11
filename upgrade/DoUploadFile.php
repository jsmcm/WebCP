<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

//print $_SERVER["REMOTE_ADDR"]."<br>";

if( ! strstr($_SERVER["REMOTE_ADDR"], "127.0.0"))
{
	require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

	$ClientID = $oUser->getClientId();
	if($ClientID < 1)
	{
	        header("Location: /index.php");
	        exit();
	}
}


	$ZipFile = "";
	if(isset($_GET["ZipFile"]))
	{
		$ZipFile = $_GET["ZipFile"];
	}

	if($ZipFile == "")
	{
		header("location: index.php?Notes=Invalid upgrade file specified!");
		exit();
	}
	chmod($ZipFile, 0755);

	if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/upgrade/db_backups") )
	{
		mkdir($_SERVER["DOCUMENT_ROOT"]."/upgrade/db_backups/", 0755);
		chmod($_SERVER["DOCUMENT_ROOT"]."/upgrade/db_backups/", 0755);
	}

	require_once($_SERVER["DOCUMENT_ROOT"]."/upgrade/includes/functions.php");

	function chmod_R($path, $filemode, $dirmode) 
	{
    		if (is_dir($path) ) 
		{
        		if (!chmod($path, $dirmode)) 
			{
            			$dirmode_str=decoct($dirmode);
            			print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
            			print "  `-> the directory '$path' will be skipped from recursive chmod\n";
            			return;
        		}
        
			$dh = opendir($path);
        
			while (($file = readdir($dh)) !== false) 
			{
            			if($file != '.' && $file != '..') 
				{  // skip self and parent pointing directories 
                			$fullpath = $path.'/'.$file;
                			chmod_R($fullpath, $filemode,$dirmode);
            			}
        		}
        		
			closedir($dh);
    		} 
		else 
		{
        		if (is_link($path)) 
			{
            			print "link '$path' is skipped\n";
            			return;
        		}
        
			if (!chmod($path, $filemode)) 
			{
            			$filemode_str=decoct($filemode);
            			print "Failed applying filemode '$filemode_str' on file '$path'\n";
            			return;
        		}
    		}
	}



	if(trim(substr($ZipFile, strlen($ZipFile) - 4)) != ".zip")
	{
		header("location: index.php?Notes=Only zip files are valid, please try again!");
		unlink($ZipFile);
		exit();
	}


	do
	{
	        $RandomString = rand(0,9);
	        $RandomString = $RandomString.rand(0,9);
	        $RandomString = $RandomString.rand(0,9);
	        $RandomString = $RandomString.rand(0,9);
	        $RandomString = $RandomString.rand(0,9);
	        $RandomString = $RandomString.rand(0,9);

	        $TempFolder = "./temp_".date("Y-m-d_H-i-s")."_".$RandomString;

	}while(file_exists($TempFolder));

	mkdir($TempFolder, 0755);
	chmod($TempFolder, 0755);

	

	$zip = new ZipArchive;
	if($zip->open($ZipFile))
	{
		for($i=0; $i<$zip->numFiles; $i++)
		{
			$zip->getNameIndex($i);
		}

		$zip->extractTo($TempFolder);
	
		$zip->close();
	} 
	else 
	{
		unlink($ZipFile);
		header("location: index.php?Notes=Error reading zip-archive!");
	}
	
	chmod_R($TempFolder, 0755, 0755);
	
	unlink($ZipFile);

	// Check for the existance of an upgrade.run file
	if( ! file_exists($TempFolder."/upgrade.run"))
	{
		DeleteTempFolder($TempFolder);
		header("location: index.php?Notes=Not a valid upgrade archive, no upgrade.run file found!");
	}

	// Check that it has something in it!
	if(filesize($TempFolder."/upgrade.run") <  8) // words start and end must appear to start and end at least the actions section!
	{
		DeleteTempFolder($TempFolder);
		header("location: index.php?Notes=Not a valid upgrade archive, upgrade.run file empty or too small!");
	}

	// So far so good, we're ready to actually parse the upgrade.run file...
	$Reply = ParseTempFolder($TempFolder);	

	DeleteTempFolder($TempFolder);
	
	header("location: index.php?Notes=".$Reply);
?>
