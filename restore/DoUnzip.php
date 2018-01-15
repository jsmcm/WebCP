<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

if(! file_exists("./tmp"))
{
	mkdir("./tmp", 0755);
}

                if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/"))
                {
                        mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
                }

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Log.php");
$oLog = new Log();

$URL = "index.php";
if(isset($_REQUEST["URL"]))
{
	$URL = $_REQUEST["URL"];
}

$oLog->WriteLog("DEBUG", "Start /restore/DoUnzip.php");

$FileName = "";
if(isset($_REQUEST["FileName"]))
{
	$FileName = $_REQUEST["FileName"];
}
else
{
	$oLog->WriteLog("DEBUG", "FileName not set, exiting");
	header("Location: ".$URL."?NoteType=Error&Notes=Uploading file failed");
	exit();
}
$oLog->WriteLog("DEBUG", "FileName = ".$FileName);

$UserName = "";
if(isset($_REQUEST["UserName"]))
{
	$UserName = $_REQUEST["UserName"];
}
else
{
	$oLog->WriteLog("DEBUG", "UserName not set, exiting");
	header("Location: ".$URL."?NoteType=Error&Notes=Uploading file failed");
	exit();
}
$oLog->WriteLog("DEBUG", "UserName = ".$UserName);

$RandomString = "";
if(isset($_REQUEST["RandomString"]))
{
	$RandomString = $_REQUEST["RandomString"];
}
else
{
	$oLog->WriteLog("DEBUG", "RandomString not set, exiting");
	header("Location: ".$URL."?NoteType=Error&Notes=Uploading file failed");
	exit();
}
$oLog->WriteLog("DEBUG", "RandomString = ".$RandomString);

$Path = "./tmp/".$RandomString."/";
$ZipFile = "./tmp/".$RandomString."/".$FileName;

$oLog->WriteLog("DEBUG", "Path: ./tmp/".$RandomString."/");
$oLog->WriteLog("DEBUG", "ZipFile: ./tmp/".$RandomString."/".$FileName);

$ScriptOutput = "RandomString=".$RandomString."\n";
$ScriptOutput = $ScriptOutput."UserName=".$UserName."\n";


function DeleteDirectoryRecursive($dir)
{
        if (!file_exists($dir))
        {
                return true;
        }

        if (!is_dir($dir) || is_link($dir))
        {
                return unlink($dir);
        }

        foreach (scandir($dir) as $item)
        {
                if ($item == '.' || $item == '..')
                {
                        continue;
                }

                if (!DeleteDirectoryRecursive($dir . "/" . $item))
                {
                        chmod($dir . "/" . $item, 0777);

                        if (!DeleteDirectoryRecursive($dir . "/" . $item))
                        {
                                return false;
                        }
                }
        }

        return rmdir($dir);
}


        function chmod_R($path, $filemode, $dirmode)
        {
                if (is_dir($path) )
                {
                        if (!chmod($path, $dirmode))
                        {
                                $dirmode_str=decoct($dirmode);
                                //print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
                                //print "  `-> the directory '$path' will be skipped from recursive chmod\n";
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
                                //print "link '$path' is skipped\n";
                                return;
                        }

			//print "Path: ".$path."<p>";
                        if (!chmod($path, $filemode))
                        {
                                $filemode_str=decoct($filemode);
                                //print "Failed applying filemode '$filemode_str' on file '$path'\n";
                                return;
                        }
                }
        }

	
	// First extract the tars
	if( ! file_exists("./tmp/".$RandomString."/tarballs"))
	{
		mkdir("./tmp/".$RandomString."/tarballs", 0755);
	}
	
	$oLog->WriteLog("DEBUG", "/bin/tar xvfz $ZipFile -C ./tmp/".$RandomString."/tarballs");
	`/bin/tar xvfz $ZipFile -C ./tmp/$RandomString`;

	//$oLog->WriteLog("DEBUG", "/bin/tar xvfz $ZipFile -C $Path");
	//`/bin/tar xvfz $ZipFile -C $Path`;
	

	chmod_R($Path, 0755, 0755);

	//unlink($ZipFile);

	if(! file_exists("./tmp/".$RandomString."/".$UserName.".xml"))
	{
		print "D;;";
		DeleteDirectoryRecursive("./tmp/".$RandomString);
		header("Location: ".$URL."?NoteType=Error&Notes=Invalid tar.gz file (1)!");
		exit();
	}	

	if(! file_exists("./tmp/".$RandomString."/".$UserName."_web.tar"))
	{
		DeleteDirectoryRecursive("./tmp/".$RandomString);
		header("Location: ".$URL."?NoteType=Error&Notes=Invalid tar file (2)!");
		exit();
	}	


	if(file_exists("/home/".$UserName))
	{
		DeleteDirectoryRecursive("./tmp/".$RandomString);
		header("Location: ".$URL."?NoteType=Error&Notes=/home/".$UserName."/ directory already exists. You can only restore an account if it DOES NOT currently exist!");
		exit();
	}	


	$xml = '';
	$fh   = @fopen("./tmp/".$RandomString."/".$UserName.".xml", 'r');
	if ($fh)
	{
		while (!feof($fh))
		{
			$s = fread($fh, 1024);
			if (is_string($s))
			{
				$xml .= $s;
			}
		}
		fclose($fh);
	}

	$XMLFile = new SimpleXMLElement($xml);

	$PackageName = $XMLFile->Package->PackageName;
	$Emails = $XMLFile->Package->Emails;
	$Domains = $XMLFile->Package->Domains;
	$SubDomains = $XMLFile->Package->SubDomains;
	$ParkedDomains = $XMLFile->Package->ParkedDomains;
	$DiskSpace = $XMLFile->Package->DiskSpace;
	$Traffic = $XMLFile->Package->Traffic;
	$FTP = $XMLFile->Package->FTP;
	$MySQL = $XMLFile->Package->MySQL;
	$PostgreSQL = $XMLFile->Package->PostgreSQL;

	$oLog->WriteLog("DEBUG", "PackageName = ".$PackageName);
	$oLog->WriteLog("DEBUG", "Emails = ".$Emails);
	$oLog->WriteLog("DEBUG", "Domains = ".$Domains);
	$oLog->WriteLog("DEBUG", "SubDomains = ".$SubDomains);
	$oLog->WriteLog("DEBUG", "ParkedDomains = ".$ParkedDomains);
	$oLog->WriteLog("DEBUG", "DiskSpace = ".$DiskSpace);
	$oLog->WriteLog("DEBUG", "Traffic = ".$Traffic);
	$oLog->WriteLog("DEBUG", "FTP = ".$FTP);
	$oLog->WriteLog("DEBUG", "MySQL = ".$MySQL);
	$oLog->WriteLog("DEBUG", "PostgreSQL = ".$PostgreSQL);


	// check if this package already exists, even if it has a different name
	
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Package.php");
	$oPackage = new Package();
		
	$PackageArray = array();
	$ArrayCount = 0;

	$oPackage->GetPackageList($PackageArray, $ArrayCount, "admin", 1);

	for($x = 0; $x < $ArrayCount; $x++)
	{
		$PackageDetailArrayCount = 0;
		$PackageDetailArray = array();

		$oLog->WriteLog("DEBUG", "Getting Package Details for package: ".$PackageArray[$x]["package_id"]);
		$oPackage->GetPackageDetails($PackageArray[$x]["package_id"], $PackageDetailArray, $PackageDetailArrayCount, "admin", 0);

		//print_r($PackageDetailArray);
		//print "<p>";

		$Match = 1;

		foreach($PackageDetailArray as $key=>$val)
		{
			switch($key)
			{
				case "Emails":
				{
					//print "val = ".$val."; Scrtip: ".$Emails."<br>";
				
					if($val != $Emails)
					{
						// no match!
						$Match = 0;
					}

					break;
				}
				
				case "Domains":
				{
					//print "val = ".$val."; Scrtip: ".$Domains."<br>";
				
					if($val != $Domains)
					{
						// no match!
						$Match = 0;
					}

					break;
				}

				case "SubDomains":
				{
					//print "val = ".$val."; Scrtip: ".$SubDomains."<br>";
				
					if($val != $SubDomains)
					{
						// no match!
						$Match = 0;
					}

					break;
				}

				case "ParkedDomains":
				{
					//print "val = ".$val."; Scrtip: ".$ParkedDomains."<br>";
				
					if($val != $ParkedDomains)
					{
						// no match!
						$Match = 0;
					}

					break;
				}


                                case "DiskSpace":
                                {
					
                                        if($val != $DiskSpace)
                                        {
                                                // no match!
                                                $Match = 0;
                                        }

                                        break;
                                }
                                case "Traffic":
                                {

                                        if($val != $Traffic)
                                        {
                                                // no match!
                                                $Match = 0;
                                        }

                                        break;
                                }
                                case "FTP":
                                {
		
                                        if($val != $FTP)
                                        {
                                                // no match!
                                                $Match = 0;
                                        }

                                        break;
                                }
                                case "MySQL":
                                {

                                        if($val != $MySQL)
                                        {
                                                // no match!
                                                $Match = 0;
                                        }

                                        break;
                                }
                                case "PostgreSQL":
                                {

                                        if($val != $PostgreSQL)
                                        {
                                                // no match!
                                                $Match = 0;
                                        }

                                        break;
                                }


			}


			if($Match == 0)
			{
				break;
			}
		}

		//print "<p>";

		if($Match == 1)
		{
			// we got a package match!
			$oLog->WriteLog("DEBUG", "Package Match!");
			break;
		}
	}

	$PackageID = 0;

	if($Match == 0)
	{
		//print "no package match, create it now<br>";
		$oLog->WriteLog("DEBUG", "No Package Match!");

		$x = 1;
		while($oPackage->PackageExists($PackageName))
		{
			$PackageName = $PackageName.$x++;
		}

		//print "Creating Package ".$PackageName."<br>";

		$Settings = array();
		$Settings["Emails"] = $Emails;
		$Settings["Domains"] = $Domains;
		$Settings["SubDomains"] = $SubDomains;
		$Settings["ParkedDomains"] = $ParkedDomains;
		$Settings["DiskSpace"] = $DiskSpace;
		$Settings["Traffic"] = $Traffic;
		$Settings["FTP"] = $FTP;
		$Settings["MySQL"] = $MySQL;
		$Settings["PostgreSQL"] = $PostgreSQL;
	
		$PackageID = $oPackage->AddPackage($PackageName, $Settings);

	}
	else
	{
		//print "Package match, use it (".$PackageArray[$x]["package_id"].")!<br>";
		$PackageID = $PackageArray[$x]["package_id"];
	}


	$oLog->WriteLog("DEBUG", "Continuing with PackageID: ".$PackageID);


	$FirstName = $XMLFile->User->FirstName;		
	$Surname = $XMLFile->User->Surname;		
	$EmailAddress = $XMLFile->User->EmailAddress;		
	$Role = $XMLFile->User->Role;		
	$UserName = $XMLFile->User->UserName;		
	$Password = $XMLFile->User->Password;		

	$oLog->WriteLog("DEBUG", "FirstName: ".$FirstName);
	$oLog->WriteLog("DEBUG", "Surname: ".$Surname);
	$oLog->WriteLog("DEBUG", "EmailAddress: ".$EmailAddress);
	$oLog->WriteLog("DEBUG", "Role: ".$Role);
	$oLog->WriteLog("DEBUG", "UserName: ".$UserName);
	$oLog->WriteLog("DEBUG", "Password: ".$Password);

	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
	$oUser = new User();

	$UserID = $oUser->UserExistsByEmail($EmailAddress);

	$oLog->WriteLog("DEBUG", "UserID = ".$UserID);

	if($UserID < 1)
	{
		$oLog->WriteLog("DEBUG", "AddUser('".$FirstName."', '".$Surname."', '".$EmailAddress."', '".$Password."', '".$Role."', '".$UserName."', 0)");
		
		$UserID = $oUser->AddUser($FirstName, $Surname, $EmailAddress, $Password, $Role, $UserName, 0);
		$oUser->PlainTextChangePassword($Password, $UserID);
		$oLog->WriteLog("DEBUG", "UserID = ".$UserID);
	}

	//print "UserID: ".$UserID."<br>";


	//print "<p><hr><p>";

	$DomainArrayID = array();
	for($x = 0; $x < $XMLFile->Domain->Instance->count(); $x++)
	{
		//print $XMLFile->Domain->Instance[$x]->ID."<br>";
		array_push($DomainArrayID, $XMLFile->Domain->Instance[$x]->ID);
	}


	//print_r($DomainArrayID);
	//print "<p>";
	
	sort($DomainArrayID, SORT_NUMERIC);
	//print_r($DomainArrayID);

	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
	$oDomain = new Domain();

	$PrimaryDomainID = 0;

	for($x = 0; $x < count($DomainArrayID); $x++)
	{
				
		for($y = 0; $y < $XMLFile->Domain->Instance->count(); $y++)
		{
			if($XMLFile->Domain->Instance[$y]->ID == $DomainArrayID[$x])
			{
				$oLog->WriteLog("DEBUG", "DomainName: ".$XMLFile->Domain->Instance[$y]->DomainName);
				$oLog->WriteLog("DEBUG", "DomainType: ".$XMLFile->Domain->Instance[$y]->type);
	
				if($XMLFile->Domain->Instance[$y]->type == "primary")
				{
					if($PrimaryDomainID > 0)
					{
						DeleteDirectoryRecursive("./tmp/".$RandomString);
						$oLog->WriteLog("DEBUG", "Zip file contains multiple primary domains");
						header("Location: ".$URL."?NoteType=Error&Notes=Error, this file contains multiple primary domains!");
						exit();
					}
				}
		
				if($XMLFile->Domain->Instance[$y]->type != "subdomain")
				{
					if($oDomain->DomainExists($XMLFile->Domain->Instance[$y]->DomainName) > -1)
					{
						DeleteDirectoryRecursive("./tmp/".$RandomString);
						$oLog->WriteLog("DEBUG", "Domain already exists");
						header("Location: ".$URL."?NoteType=Error&Notes=Domain already exists, you can only restore if the domains (including sub and parked domains) does not exist!");
						exit();
					}
				}
		
				
				if($XMLFile->Domain->Instance[$y]->type == "primary")
				{
					$DomainName = $XMLFile->Domain->Instance[$y]->DomainName." ";
	
					$oLog->WriteLog("DEBUG", "AddDomain('".trim($DomainName)."', '".$XMLFile->Domain->Instance[$y]->type."', ".$PackageID.", ".$UserID.")");

					$PrimaryDomainID = $oDomain->AddDomain(trim($DomainName), $XMLFile->Domain->Instance[$y]->type, $PackageID, $UserID, $Error);
	
					$oLog->WriteLog("DEBUG", "PrimaryDomainID: ".$PrimaryDomainID);

					if($PrimaryDomainID < 1)
					{
						DeleteDirectoryRecursive("./tmp/".$RandomString);
						//print "Could not add primary domain<bR>";
						$oLog->WriteLog("DEBUG", "Could not add primary domain");
						header("Location: ".$URL."?NoteType=Error&Notes=Error, could not add primary domain!");
						exit();
					}

				}
				else if($XMLFile->Domain->Instance[$y]->type == "subdomain")
				{
					$ParentDomainID = $oDomain->GetDomainIDFromDomainName($XMLFile->Domain->Instance[$y]->ParentDomain);

					$oLog->WriteLog("DEBUG", "ParentDomain: ".$oDomain->GetDomainIDFromDomainName($XMLFile->Domain->Instance[$y]->ParentDomain));
					$oLog->WriteLog("DEBUG", "ParentDomainID: ".$ParentDomainID);
					if($ParentDomainID < 1)
					{
						DeleteDirectoryRecursive("./tmp/".$RandomString);
						//print "Could not add subdomain<bR>";
						$oLog->WriteLog("DEBUG", "Could not add subdomain");
						header("Location: ".$URL."?NoteType=Error&Notes=Error, could not add subdomain!");
						exit();
					}

					$oLog->WriteLog("DEBUG", "AddSubDomain(".substr($XMLFile->Domain->Instance[$y]->DomainName.", 0, ".strpos($XMLFile->Domain->Instance[$y]->DomainName, ".") ).", ".$ParentDomainID.", ".$UserID.", ".$Error.")");
					$oDomain->AddSubDomain(substr($XMLFile->Domain->Instance[$y]->DomainName, 0, strpos($XMLFile->Domain->Instance[$y]->DomainName, ".") ), $ParentDomainID, $UserID, $Error);

				}
				else if($XMLFile->Domain->Instance[$y]->type == "parked")
				{
					$ParentDomainID = $oDomain->GetDomainIDFromDomainName($XMLFile->Domain->Instance[$y]->ParentDomain);

					$oLog->WriteLog("DEBUG", "ParentDomainID: ".$ParentDomainID);
					if($ParentDomainID < 1)
					{
						DeleteDirectoryRecursive("./tmp/".$RandomString);
						$oLog->WriteLog("DEBUG", "Could not add parked domain");
						//print "Could not add parked domain<bR>";
						header("Location: ".$URL."?Notes=Error, could not add parked domain!");
						exit();
					}
					
					$s = "AddParkedDomain(".$XMLFile->Domain->Instance[$y]->DomainName.", ".$XMLFile->Domain->Instance[$y]->ParentDomain.", ".$PackageID.", ".$UserID.", ".$ParentDomainID.", ".$Error.");";
					$oLog->WriteLog("DEBUG", $s);
			
					$oDomain->AddParkedDomain($XMLFile->Domain->Instance[$y]->DomainName, $XMLFile->Domain->Instance[$y]->ParentDomain, $PackageID, $UserID, $ParentDomainID, $Error);

				}
				
			}
		}
	}


	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.MySQL.php");
	$oMySQL = new MySQL();

	for($x = 0; $x < $XMLFile->MySQL->Instance->count(); $x++)
	{
		$oLog->WriteLog("DEBUG", "Calling AddMySQL(".$PrimaryDomainID.", ".$XMLFile->MySQL->Instance[$x]->DatabaseName.", ".$XMLFile->MySQL->Instance[$x]->DatabaseUsername.", ".str_replace("&amp;", "&", $XMLFile->MySQL->Instance[$x]->Password).", ".$UserID.", ".$PackageID.")");

		$MySQLResult = $oMySQL->AddMySQL($PrimaryDomainID, $XMLFile->MySQL->Instance[$x]->DatabaseName, $XMLFile->MySQL->Instance[$x]->DatabaseUsername, str_replace("&amp;", "&", $XMLFile->MySQL->Instance[$x]->Password), $UserID, $PackageID);		
		
		$oLog->WriteLog("DEBUG", "MySQLResult = '".$MySQLResult."'");

		$ScriptOutput = $ScriptOutput."MySQL=".$XMLFile->MySQL->Instance[$x]->DatabaseName."\n";
	}



	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
	$oEmail = new Email();

	for($x = 0; $x < $XMLFile->Email->Instance->count(); $x++)
	{
		$DomainID = $oDomain->GetDomainIDFromDomainName($XMLFile->Email->Instance[$x]->fqdn);
		//print "DomainID: ".$DomainID."<br>";

		$oEmail->AddEmailPlainPassword($XMLFile->Email->Instance[$x]->local_part, $DomainID, $XMLFile->Email->Instance[$x]->Password, $UserID);
	}



        for($x = 0; $x < $XMLFile->AutoReply->Instance->count(); $x++)
        {
		$EmailID = $oEmail->GetMailBoxID($XMLFile->AutoReply->Instance[$x]->EmailAddress);
		
		$oEmail->AddAutoReply($UserID, $EmailID, $XMLFile->AutoReply->Instance[$x]->Subject, $XMLFile->AutoReply->Instance[$x]->Body, $XMLFile->AutoReply->Instance[$x]->Frequency, $XMLFile->AutoReply->Instance[$x]->StartDate, $XMLFile->AutoReply->Instance[$x]->EndDate);
        }





	for($x = 0; $x < $XMLFile->EmailForwarding->Instance->count(); $x++)
	{
		$oEmail->AddSingleForward($XMLFile->EmailForwarding->Instance[$x]->LocalPart, $DomainID, $XMLFile->EmailForwarding->Instance[$x]->ForwardTo, $UserID);
	}



	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.FTP.php");
	$oFTP = new FTP();

	for($x = 0; $x < $XMLFile->FTP->Instance->count(); $x++)
	{
		$ID = $oFTP->AddFTP(substr($XMLFile->FTP->Instance[$x]->UserName, strpos($XMLFile->FTP->Instance[$x]->UserName, "_") + 1), $PrimaryDomainID, 'Password', $XMLFile->FTP->Instance[$x]->QuotaSize, $UserID);	
		$oFTP->PlainPasswordEdit($ID, $XMLFile->FTP->Instance[$x]->Password);

	}


	$fp = fopen("../nm/".$RandomString.".restore", "w");
	fwrite($fp, $ScriptOutput);
	fclose($fp);

	$oLog->WriteLog("DEBUG", "Done /restore/DoUnzip.php, redirecting now");

	header("Location: ".$URL."?NoteType=Success&Notes=Restore succeeded.<br><b>It may be a few minutes before all files are available, please be patient</b>!");
	exit();

?>
