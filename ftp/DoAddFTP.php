<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.FTP.php");
$oFTP = new FTP();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Package.php");
$oPackage = new Package();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$UserName = filter_var($_POST["UserName"], FILTER_SANITIZE_STRING);
$DomainUserName = filter_var($_POST["DomainUserName"], FILTER_SANITIZE_STRING);
$DomainID = filter_var($_POST["DomainID"], FILTER_SANITIZE_STRING);
$Password = filter_var($_POST["Password"], FILTER_SANITIZE_STRING);

$Role = $oUser->Role;

//print "Domain User Name: ".$DomainUserName."<br>";
//print "Role: ".$Role."<br>";
//print "Client ID: ".$ClientID."<br>";
//print "DomainID: ".$DomainID."<br>";

for($x = 0; $x < strlen($UserName); $x++)
{
				
		
	if(!ctype_alnum($UserName[$x]))
	{
		if($UserName[$x] != '_' && $UserName[$x] != '-' && $UserName[$x] != '.')
		{
			header("location: index.php?NoteType=Error&Notes=Incorrectly formatted FTP user name");
			exit();
		}
		
	}
}



if($oFTP->FTPExists($DomainUserName."_".$UserName) > 0)
{
	header("location: index.php?NoteType=Error&Notes=The username already exists, please try another");
	exit();
}

$DomainInfoArray = array();
$oDomain->GetDomainInfo($DomainID, $DomainInfoArray);

$PackageID = $DomainInfoArray["PackageID"];
$Mb = $oPackage->GetPackageAllowance("DiskSpace", $PackageID);

//print "User Name: ".$UserName."<p>";
//print "PackageID: ".$PackageID."<p>";
//print "Mb: ".$Mb."<p>";
///print "Password: ".$Password."<p>";

$x = $oFTP->AddFTP($UserName, $DomainID, $Password, $Mb, $DomainInfoArray["ClientID"]);

//print "x: ".$x."<p>";

if($x < 1)
{ 
	$Message = "Cannot add FTP user";

	if($x == -1)
	{
		$Message = "You do not have any more FTP users on your current hosting plan";
	}
//print $Message;
	header("location: index.php?NoteType=Error&Notes=".$Message);
	exit();
}
//print "done";
header("location: index.php?Notes=FTP user name added");

?>


