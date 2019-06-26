<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();
$oLog = new Log();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$serverAccountsCreated = $oDomain->GetAccountsCreatedCount();
$serverAccountsAllowed = $validationArray["allowed"];
$serverLicenseType = $validationArray["type"];


if ( $serverLicenseType == "free" && ($serverAccountsCreated >= $serverAccountsAllowed) ) {
        header("Location: index.php?Notes=".htmlentities("You are on a free license. Please upgrade to add more accounts")."&NoteType=error");
        exit();
}


$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$ClientID_requesting_domain = $_REQUEST["ClientID"];
$DomainName = $_REQUEST["DomainName"];
$Role = $oUser->Role;

$PackageID = $_REQUEST["PackageID"];

if( ($ClientID != $ClientID_requesting_domain) && ($Role == 'client'))
{
	header("location: index.php?NoteType=Error&Notes=Permission denied");
	exit();
}

if($oDomain->ValidateDomainName($DomainName) < 1)
{
	header("Location: index.php?NoteType=Error&Notes=Incorrectly formatted domain name - <b>".$DomainName."</b>");
	exit();
}

if(substr($DomainName, 0, 7) == "http://")
{
	$DomainName = substr($DomainName, 7);
}

if(substr($DomainName, 0, 4) == "www.")
{
	$DomainName = substr($DomainName, 4);
}


for($x = 0; $x < strlen($DomainName); $x++)
{
				
		
	if(!ctype_alnum($DomainName[$x]))
	{
		if($DomainName[$x] != '_' && $DomainName[$x] != '-' && $DomainName[$x] != '.')
		{
			header("location: index.php?NoteType=Error&Notes=Incorrectly formatted domain name");
			exit();
		}
		
	}
}




if($oDomain->DomainExists($DomainName) > 0)
{
	header("location: index.php?NoteType=Error&Notes=Domain name already exists");
	exit();
}

$Error = "";


$oLog->WriteLog("DEBUG", "In /domains/DoAddDomain.php - > AddDomain('".$DomainName."', '".$_POST["DomainType"]."',".$PackageID.",".$ClientID_requesting_domain.")");

$domainId = $oDomain->AddDomain($DomainName, $_POST["DomainType"], $PackageID, $ClientID_requesting_domain, $Error);

if ($domainId < 1 ) {
	header("location: index.php?NoteType=Error&Notes=Cannot add domain");
	exit();
}

$infoArray = array();
$oDomain->GetDomainInfo($domainId, $infoArray);

file_put_contents(dirname(__DIR__)."/nm/".$DomainName.".freessl_tmp", "PrimaryDomainID=".$domainId."\nType=".$_POST["DomainType"]."\nPath=".$infoArray["Path"]."\nDomainID=".$domainId."\nDomainName=".$DomainName."\nDomainUserName=".$infoArray["UserName"]."\nEmailAddress=".$oUser->EmailAddress."\n");

$oLog->WriteLog("DEBUG", "AddDomain Succeeded");

header("location: index.php?NoteType=Message&Notes=Domain added<br><b>Please wait 1 minute before adding email or FTP accounts for this domains!</b>".$Error);


