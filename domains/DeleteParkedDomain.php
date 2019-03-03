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
$ParkedDomainOwnerClientID = $oDomain->GetDomainOwner($_REQUEST["ParkedDomainID"]);

if( ($ClientID != $ParkedDomainOwnerClientID) && ($oUser->Role != 'admin') )
{	
	header("location: index?Notes=No%20Permission!!!");
	exit();
}

//print "ParkedDomainOwnerClientID: ".$ParkedDomainOwnerClientID."<br>";
//print "ParkedDomainID: ".$_REQUEST["ParkedDomainID"]."<br>";

if($oDomain->DeleteParkedDomain($ParkedDomainOwnerClientID, $_REQUEST["ParkedDomainID"], $Error) == 1)
{
	$Notes="Parked Domain Deleted";
}
else
{	
	$Notes="Parked Domain cannot be deleted";
}


header("location: index.php?Notes=".$Notes.$Error);	

