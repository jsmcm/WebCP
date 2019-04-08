<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$oDomain = new Domain();
$DomainOwnerClientID = $oDomain->GetDomainOwner($_REQUEST["DomainID"]);

if( ($ClientID != $DomainOwnerClientID) && ($oUser->Role != 'admin') )
{	
	header("location: index?Notes=No%20Permission!!!");
	exit();
}

//print "DomainOwnerClientID: ".$DomainOwnerClientID."<br>";
//print "DomainID: ".$_REQUEST["DomainID"]."<br>";

if($oDomain->DeleteDomain($DomainOwnerClientID, $_REQUEST["DomainID"], $Error) == 1)
{
	$Notes="Domain Deleted";
}
else
{	
	$Notes="Domain cannot be deleted";
}


header("location: index.php?Notes=".$Notes.$Error);	
