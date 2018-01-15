<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Package.php");


$oUser = new User();
$oDomain = new Domain();
$oPackage = new Package();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /ListParkedDomains.php");
        exit();
}



$DomainID = $_REQUEST["DomainID"];
$ParkedDomain = $_REQUEST["ParkedDomain"];
$Role = $oUser->Role;
$DomainOwner = $oDomain->GetDomainOwner($DomainID);
$PrimaryDomain = $oDomain->GetDomainNameFromDomainID($DomainID);



$ParkedDomainUsage = $oPackage->GetParkedDomainUsage($oUser->UserName);
$DomainInfoArray = array();


$oDomain->GetDomainInfo($DomainID, $DomainInfoArray);

$DomainUserName = $DomainInfoArray["UserName"];
$PackageID = $DomainInfoArray["PackageID"];

$ParkedDomainAllowance = $oPackage->GetPackageAllowance("ParkedDomains", $PackageID);
$ParkedDomainUsage = $oPackage->GetParkedDomainUsage($DomainUserName);
$AncestorDomainID = $oDomain->GetAncestorDomainID($DomainID);


if( ($ParkedDomainUsage >= $ParkedDomainAllowance) && ($oUser->Role != "admin") )
{
	header("Location: ListParkedDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=You have no more parked domains available");
	exit();
}


if( ($ClientID != $DomainOwner) && ($Role != 'admin'))
{
	header("location: ListParkedDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Permission denied");
	exit();
}

if( substr($ParkedDomain, strlen($ParkedDomain) - 1, 1) == ".")
{
	// no . can't be a domain
	header("Location: ListParkedDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Incorrectly formatted domain name");
	exit();
}

if(!strstr($ParkedDomain, "."))
{
	// no . can't be a domain
	header("Location: ListParkedDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Incorrectly formatted domain name");
	exit();
}

if(substr($ParkedDomain, 0, 7) == "http://")
{
	$ParkedDomain = substr($ParkedDomain, 7);
}

if(substr($ParkedDomain, 0, 4) == "www.")
{
	$ParkedDomain = substr($ParkedDomain, 4);
}


for($x = 0; $x < strlen($ParkedDomain); $x++)
{
				
		
	if(!ctype_alnum($ParkedDomain[$x]))
	{
		if($ParkedDomain[$x] != '_' && $ParkedDomain[$x] != '-' && $ParkedDomain[$x] != '.')
		{
			header("location: ListParkedDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Incorrectly formatted domain name");
			exit();
		}
		
	}
}


if($oDomain->DomainExists($ParkedDomain) > 0)
{
	header("location: ListParkedDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Domain name already exists");

	exit();
}

/*
print "ClientID: ".$ClientID."<br>";
print "DomainID: ".$DomainID."<br>";
print "PrimaryDomain: ".$PrimaryDomain."<br>";
print "ParkedDomain: ".$ParkedDomain."<br>";
print "Role: ".$Role."<br>";
print "DomainOwner: ".$DomainOwner."<br>";
print "ParkedDomainUsage: ".$ParkedDomainUsage."<br>";
print "ParkedDomainAllowance: ".$ParkedDomainAllowance."<br>";
print "PackageID: ".$PackageID."<br>";
exit();
*/

$Error = "";
if($oDomain->AddParkedDomain($ParkedDomain, $PrimaryDomain, $PackageID, $DomainOwner, $DomainID, $Error) < 1)
{
	header("location: ListParkedDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Cannot add domain");
	exit();
}
header("location: ListParkedDomains.php?DomainID=".$AncestorDomainID."&NoteType=Success&Notes=Domain added<br><b>Please wait 1 minute before adding email or FTP accounts for this domains!</b>".$Error);

?>

i
