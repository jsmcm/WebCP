<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Log.php");
$oLog = new Log();

function DeleteTempFolder($TempFolder)
{
        if (is_dir($TempFolder) )
   	{

                $dh = opendir($TempFolder);

                while (($file = readdir($dh)) !== false)
           	{
                        if($file != '.' && $file != '..')
                       	{  
                                $fullTempFolder = $TempFolder.'/'.$file;
                        	DeleteTempFolder($fullTempFolder);
                	}
	    	}
        		
	       	closedir($dh);

		rmdir($TempFolder);
        }
        else
       	{
               	unlink($TempFolder);
	}
}

function CheckVersion($CurrentVersion, $RequiredVersion, $RequiredVersionCondition)
{
	$CurrentVersionMajor = 0;
	$CurrentVersionMinor = 0;
	$CurrentVersionRevision = 0;
	
	$RequiredVersionMajor = 0;
	$RequiredVersionMinor = 0;
	$RequiredVersionRevision = 0;

	$CurrentVersionMajor = trim(substr($CurrentVersion, 0, strpos($CurrentVersion, ".")));
	$CurrentVersion = substr($CurrentVersion, strlen($CurrentVersionMajor) + 1);

	$CurrentVersionMinor = trim(substr($CurrentVersion, 0, strpos($CurrentVersion, ".")));
	$CurrentVersion = substr($CurrentVersion, strlen($CurrentVersionMinor) + 1);

	$CurrentVersionRevision = trim($CurrentVersion);

	$RequiredVersionMajor = trim(substr($RequiredVersion, 0, strpos($RequiredVersion, ".")));
	$RequiredVersion = substr($RequiredVersion, strlen($RequiredVersionMajor) + 1);

	$RequiredVersionMinor = trim(substr($RequiredVersion, 0, strpos($RequiredVersion, ".")));
	$RequiredVersion = substr($RequiredVersion, strlen($RequiredVersionMinor) + 1);

	$RequiredVersionRevision = trim($RequiredVersion);

	//print "CurrentVersionMajor: '".$CurrentVersionMajor."'<br>";
	//print "CurrentVersionMinor: '".$CurrentVersionMinor."'<br>";
	//print "CurrentVersionRevision: '".$CurrentVersionRevision."'<br>";
	//print "RequiredVersionMajor: '".$RequiredVersionMajor."'<br>";
	//print "RequiredVersionMinor: '".$RequiredVersionMinor."'<br>";
	//print "RequiredVersionRevision: '".$RequiredVersionRevision."'<br>";
	//print "RequiredVersionCondition: '".$RequiredVersionCondition."'<br>";

	switch($RequiredVersionCondition)
	{
		case "=":
		{
			print "In equals....<br>";
			
			if( ($CurrentVersionMajor == $RequiredVersionMajor) && ($CurrentVersionMinor == $RequiredVersionMinor) && ($CurrentVersionRevision == $RequiredVersionRevision) )
			{
				return true;
			}

			return false;

			break;
		}
		
		case "!":
		{
			print "In not equals....<br>";
			
			if( ($CurrentVersionMajor == $RequiredVersionMajor) && ($CurrentVersionMinor == $RequiredVersionMinor) && ($CurrentVersionRevision == $RequiredVersionRevision) )
			{
				return false;
			}

			return true;

			break;
		}

		case ">":
		{
			if($CurrentVersionMajor < $RequiredVersionMajor)
			{
				print "Major too small, leaving!<br>";
				return false;
			}
			else if($CurrentVersionMajor > $RequiredVersionMajor)
			{
				return true;
			}
			
			// if we got here, the majors are the same, check minors
			if($CurrentVersionMinor < $RequiredVersionMinor)
			{
				print "Minor too small, leaving!<br>";
				return false;
			}
			else if($CurrentVersionMinor > $RequiredVersionMinor)
			{
				return true;
			}

			// if we got here, the minors are the same, check revision
			if($CurrentVersionRevision < $RequiredVersionRevision)
			{
				print "Minor too small, leaving!<br>";
				return false;
			}
			else if($CurrentVersionRevision > $RequiredVersionRevision)
			{
				return true;
			}

			break;
		}
		
		case "<":
		{
			if($CurrentVersionMajor > $RequiredVersionMajor)
			{
				return false;
			}
			else if($CurrentVersionMajor < $RequiredVersionMajor)
			{
				return true;
			}
			
			// if we got here, the majors are the same, check minors
			if($CurrentVersionMinor > $RequiredVersionMinor)
			{
				return false;
			}
			else if($CurrentVersionMinor < $RequiredVersionMinor)
			{
				return true;
			}

			// if we got here, the minors are the same, check revision
			if($CurrentVersionRevision > $RequiredVersionRevision)
			{
				return false;
			}
			else if($CurrentVersionRevision < $RequiredVersionRevision)
			{
				return true;
			}

			break;
		}

	}

	return false;

			
}

function ReadCurrentVersionFile()
{

	$Input = "";

	$handle = fopen($_SERVER["DOCUMENT_ROOT"]."/includes/version.inc", "r");
	if ($handle) 
	{	
		while (($line = fgets($handle)) !== false) 
		{
			$Input = $Input.trim($line);
		}

		fclose($handle);
	}
	
	return $Input; 

}


function ParseTempFolder($TempFolder)
{

	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Database.php");
	$oDatabase = new Database();	

	$UpgradeVersion = "";
		

	$InRequires = false;
	$InRequiresFiles = false;
	$InRequiresFolders = false;
	$InRequiresVersion = false;
	$InRequiresDBTables = false;
	$InRequiresDBFields = false;

	$InActions = false;
	$InActionsSQL = false;

	$handle = fopen($TempFolder."/upgrade.run", "r");
	if ($handle) 
	{	
		while (($line = fgets($handle)) !== false) 
		{
			$line = trim($line);
		
			$x = strpos($line, "//");

			if($x > -1)
			{
				$line = trim(substr($line, 0, $x));
			}
		
			$x = strpos($line, "#");
	
			if($x > -1)
			{
				$line = trim(substr($line, 0, $x));
			}
			
			if($line != "")
			{
	

				if($InRequires == true)
				{
					
					if(strtolower($line) == "end requires;")
					{
						$InRequires = false;
						$InRequiresFiles = false;
						$InRequiresFolders = false;
						$InRequiresVersion = false;
						$InRequiresDBTables = false;
						$InRequiresDBFields = false;
	
						$InActions = false;
						continue;
					}
					else if(strtolower($line) == "[files]")
					{
						$InRequiresFiles = true;
						$InRequiresFolders = false;
						$InRequiresVersion = false;
						$InRequiresDBTables = false;
						$InRequiresDBFields = false;
						continue;
					}
					else if(strtolower($line) == "[folders]")
					{
						$InRequiresFiles = false;
						$InRequiresFolders = true;
						$InRequiresVersion = false;
						$InRequiresDBTables = false;
						$InRequiresDBFields = false;
						continue;
					}
					else if(strtolower($line) == "[version]")
					{
						$InRequiresFiles = false;
						$InRequiresFolders = false;
						$InRequiresVersion = true;
						$InRequiresDBTables = false;
						$InRequiresDBFields = false;
						continue;
					}
					else if(strtolower($line) == "[db_tables]")
					{
						$InRequiresFiles = false;
						$InRequiresFolders = false;
						$InRequiresVersion = false;
						$InRequiresDBTables = true;
						$InRequiresDBFields = false;
						continue;
					}
					else if(strtolower($line) == "[db_fields]")
					{
						$InRequiresFiles = false;
						$InRequiresFolders = false;
						$InRequiresVersion = false;
						$InRequiresDBTables = false;
						$InRequiresDBFields = true;
						continue;
					}

					if($InRequiresFiles == true)
					{
						if(file_exists($_SERVER["DOCUMENT_ROOT"].$line))
						{
							if(is_dir($_SERVER["DOCUMENT_ROOT"].$line))
							{
								fclose($handle);
								return "error, required file: ".$_SERVER["DOCUMENT_ROOT"].$line." is there, but its a folder, not a file!<p>";
							}
						}
						else
						{
							fclose($handle);
							return "error, file: ".$_SERVER["DOCUMENT_ROOT"].$line." does not exists but is required<p>";
						}
					}
					else if($InRequiresFolders == true)
					{
						if(file_exists($_SERVER["DOCUMENT_ROOT"].$line))
						{
							if( ! is_dir($_SERVER["DOCUMENT_ROOT"].$line))
							{
								fclose($handle);
								return "error, required folder: ".$SERVER["DOCUMENT_ROOT"].$line." is there, but its a file, not a folder!<p>";
							}
						}
						else
						{
							fclose($handle);
							return "error, folder: ".$_SERVER["DOCUMENT_ROOT"].$line." does not exists but is required<p>";
						}
					}
					else if($InRequiresVersion == true)
					{
						//print "Checking Version: ".$line."<br>";
						
						$RequiredVersion = "";
						$RequiredVersionCondition = "=";
						$CurrentVersion = "";

						if( (substr($line, 0, 1) == ">") || (substr($line, 0, 1) == "<") || (substr($line, 0, 1) == "=") || (substr($line, 0, 1) == "!") )
						{
							$RequiredVersionCondition = substr($line, 0, 1);
							$RequiredVersion = substr($line, 1);
						}
			
						if( (substr($line, 0, 2) == ">=") || (substr($line, 0, 2) == "<=") )
						{
							$RequiredVersionCondition = substr($line, 0, 2);
							$RequiredVersion = substr($line, 2);
						}

						if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/version.inc"))
						{
							$CurrentVersion = ReadCurrentVersionFile();
							
							if(trim($CurrentVersion) == "")
							{
								fclose($handle);			
								return "error, previous version required, but version file is empty!<p>";
							}
						}
						else
						{	
							fclose($handle);			
							return "error, previous version required, but version file not found!<p>";
						}

						//print "Current Version: ".$CurrentVersion."<br>";
						//print "Required Version: ".$RequiredVersion."<br>";
						//print "Required Version Condition: ".$RequiredVersionCondition."<br>";
					
						if(CheckVersion($CurrentVersion, $RequiredVersion, $RequiredVersionCondition) == false)
						{
							fclose($handle);
							return "error, version requirements not met. Current version = ".$CurrentVersion." but ".$RequiredVersionCondition." ".$RequiredVersion." required!<p>";
						}	
					}
					else if($InRequiresDBTables == true)
					{
						//print "Checking Tables: ".$line."<br>";
						if($oDatabase->TableExists($line) == false)
						{	
							fclose($handle);			
							return "error, Database table: ".$line." required but does not exists!<p>";
						}
						
					}
					else if($InRequiresDBFields == true)
					{
						//print "Checking Fields: ".$line."<br>";

						$TableName = "";
						$FieldName = "";
					
						if(isset($TypeArray))
						{
							unset($TypeArray);
						}

						$TypeArray = array();

						$x = strpos($line, ":");
						
						if($x < 0)
						{	
							fclose($handle);			
							return "error, incorrectly formated db fields string, expect format: <b>tablename:fieldname;date_type,datetype,...</b>!<p>";
						}	
		
						$TableName = trim(substr($line, 0, $x));	
					
						$line = substr($line, $x + 1);
						$x = strpos($line, ";");
						
						if($x < 0)
						{	
							fclose($handle);			
							return "error, incorrectly formated db fields string, expect format: <b>tablename:fieldname;date_type,datetype,...</b>!<p>";
						}	
		
						$FieldName = trim(substr($line, 0, $x));	
				
						$line = trim(substr($line, $x + 1));
						
						$TypeArray = explode(",", $line);

						//print "TableName: '".$TableName."'<br>";	
						//print "FieldName: '".$FieldName."'<br>";	

						//for($x = 0; $x < count($TypeArray); $x++)
						//{
							//print "Array ".($x + 1).": ".$TypeArray[$x]."<br>";
						//}

						
						if($oDatabase->FieldExists($TableName, $FieldName, $TypeArray) == false)
						{	
							fclose($handle);			
							return "error, Database field: ".$FieldName." on table ".$TableName." required but does not exists or is not of the proper type!<p>";
						}	
					}

				}
				else if($InActions == true)
				{
					if(strtolower($line) == "end actions;")
					{
						$InRequires = false;
						$InRequiresFiles = false;
						$InRequiresFolders = false;
						$InRequiresVersion = false;
						$InRequiresDBTables = false;
						$InRequiresDBFields = false;
	
						$InActions = false;
						continue;
					}

					if(strtolower($line) == "[sql]")
					{
						$InActionsSQL = true;
						continue;
					}

					// here are the actions!
					if($InActionsSQL == true)
					{
						if($oDatabase->DoSQL($line) == false)
						{
							fclose($handle);			
							return "error, SQL failure '".$line."'!<p>";
						}	
					}

				}
				else
				{
					if(strtolower(substr($line, 0, 8)) == "version:")	
					{
						if( ($InRequires == false) && ($InActions == false) )
						{
							$UpgradeVersion = trim(substr($line, 8));
						
							if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/version.inc"))
							{
								$CurrentVersion = ReadCurrentVersionFile();
								
								if(trim($CurrentVersion) == "")
								{
									fclose($handle);			
									return "error, previous version required, but version file is empty!<p>";
								}
							}
							else
							{	
								fclose($handle);			
								return "error, previous version required, but version file not found!<p>";
							}
	
							if(CheckVersion($CurrentVersion, $UpgradeVersion, "<") == false)
							{
								fclose($handle);
								return "error, the current version (".$CurrentVersion.") is already higher or the same as this upgrade version (".$UpgradeVersion.")!<p>";
							}
						}
					}
					else if(strtolower($line) == "start requires:")
					{
						if($InActions == true)
						{
							// This can't be!
							return "error, actions section not terminated correctly!<p>";
						}
	
						$InRequires = true;
						$InRequiresFiles = false;
						$InRequiresFolders = false;
						$InRequiresVersion = false;
						$InRequiresDBTables = false;
						$InRequiresDBFields = false;
	
						$InActions = false;
					}
					else if(strtolower($line) == "start actions:")
					{
						require_once($_SERVER["DOCUMENT_ROOT"]."/includes/Variables.inc.php");
						require_once($_SERVER["DOCUMENT_ROOT"]."/upgrade/includes/classes/iam_backup.php");
					
						global $DatabaseHost;
						global $DatabaseUserName; 
						global $DatabasePassword;
						global $DatabaseName;

						$backup = new iam_backup($DatabaseHost, $DatabaseName, $DatabaseUserName, $DatabasePassword, false, false, true, $_SERVER["DOCUMENT_ROOT"]."/upgrade/db_backups/".date("Y-m-d_H-i-s")."_".$CurrentVersion.".sql.gz");
						$backup->perform_backup();

						if($InRequires == true)
						{
							// This can't be!
							return "error, requires section not terminated correctly!<p>";
						}
	
						$InRequires = false;
						$InRequiresFiles = false;
						$InRequiresFolders = false;
						$InRequiresVersion = false;
						$InRequiresDBTables = false;
						$InRequiresDBFields = false;

						$InActions = true;
					}
				}
			}
    		}
	
		fclose($handle);
	
		// if we got here, all that's left to do is copy the files and folders!
		
		/*
		$dh = opendir($TempFolder);

              	while (($file = readdir($dh)) !== false)
         	{
                       	if($file != '.' && $file != '..')
                    	{  // skip self and parent pointing directories 
                               	$fullpath = $TempFolder.'/'.$file;

				if($file != "upgrade.run")
				{
                        		rename($fullpath, $_SERVER["DOCUMENT_ROOT"]."/".$file);
	                        	//print "rename(".$fullpath.", ".$_SERVER["DOCUMENT_ROOT"]."/".$file.");<p>";
				}
                	}
		}
                closedir($dh);
                */




		recurse_copy($TempFolder, $_SERVER["DOCUMENT_ROOT"], $CurrentVersion);


	
		$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/includes/version.inc", "w");
		fwrite($fp, $UpgradeVersion."\r\n");
		fclose($fp);

		return "success";

	}
	else 
	{
	    	return "error, could not read ".$TempFolder."/upgrade.run file<p>";
	}
}

function recurse_copy($src,$dst, $CurrentVersion) 
{ 
	$dir = opendir($src); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) 
	{     
		if (( $file != '.' ) && ( $file != '..' )) 
		{ 
                	if ( is_dir($src . '/' . $file) ) 
			{ 
                    		recurse_copy($src . '/' . $file,$dst . '/' . $file, $CurrentVersion); 
				chmod($dst."/".$file, 0755); 	
                	} 
                	else 
			{ 
				if($file != "upgrade.run")
				{
					if(file_exists($dst."/".$file))
					{
							
						$OriginalFile = $dst."/".$file;
						$BackupFile = $dst."/".$file."_".date("Y-m-d_H-i-s")."_".$CurrentVersion;

						copy($OriginalFile, $BackupFile);
						chmod($OriginalFile, 0755); 	
						chmod($BackupFile, 0000);

						unlink($OriginalFile);
 
						if(file_exists($OriginalFile))
						{
							// Log an error
							$oLog->WriteLog("error", "Upgrade -> Cannot remove original file: ".$OriginalFile);
						}	
					}

	 		      		copy($src . '/' . $file,$dst . '/' . $file);
					chmod($dst."/".$file, 0755); 	
				}
                	}	 
            	} 
        } 
        closedir($dir); 
} 

?>
