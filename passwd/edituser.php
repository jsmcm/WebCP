<?php
session_start();


require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();



	$UserArray = array();

	$UserName = $_POST["UserName"];
	$Password = $_POST["Password"];
	$Confirm = $_POST["ConfirmPassword"];
	$PasswordPath = $_POST["PasswordPath"];	
	$URL = $_POST["URL"];
	$Path = $_POST["Path"];

//print "ClientID: ".$oUser->ClientID."<br>";
//print "Domain Owner: ".$oDomain->GetDomainOwnerFromDomainName($URL)."<p>";
//exit();

if( ($oDomain->GetDomainOwnerFromDomainName($URL) != $oUser->ClientID) && ($oUser->Role != "admin") )
{
        header("location: index.php?Notes=You do not have permission to access this sites detail");
        exit();
}



	if($Password != $Confirm)
	{
		header("location: manage.php?URL=".$URL."&Path=".$Path."&Notes=Passwords dont match!");
		exit();
	}

	$Password = crypt($Password);
		
	if(file_exists($PasswordPath))
        {
	
		if(filesize($PasswordPath) > 0)
		{
                	$UserArray = explode("\n", file_get_contents($PasswordPath));
		}
        }

	$UserChanged = false;

	for($x = 0; $x < count($UserArray); $x++)
	{
		if(substr($UserArray[$x], 0, strlen($UserName) + 1) == $UserName.":")
		{
			// Change the password
			$UserArray[$x] = $UserName.":".$Password;
			
			$UserChanged = true;
		}
	}

	if($UserChanged == false)
	{
		// add this user
		array_push($UserArray, $UserName.":".$Password);
	}

	$f = fopen($PasswordPath, "w");
	
	for($x = 0; $x < count($UserArray); $x++)
	{
		while( substr($UserArray[$x], strlen($UserArray[$x]) - 1, 1) == '\n')
		{
			$UserArray[$x] = substr($UserArray, 0, strlen($UserArray) - 1);
		}

		if(strlen($UserArray[$x]) > 0)
		{
			fwrite($f, $UserArray[$x]."\n");
		}
	}

	fclose($f);


	header("location: manage.php?URL=".$URL."&Path=".$Path."&Notes=User Saved");

?>
