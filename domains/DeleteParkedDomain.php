<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

$parkedDomainId = intVal($_REQUEST["ParkedDomainID"]);
$oDomain = new Domain();

$random = random_int(1, 100000);
$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
	$parkedDomainId,
	$random
];

$oSimpleNonce = new SimpleNonce();
$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
$ParkedDomainOwnerClientID = $oDomain->GetDomainOwner($parkedDomainId, $random, $nonce);

if( ($ClientID != $ParkedDomainOwnerClientID) && ($oUser->Role != 'admin') ) {	
	header("location: index?Notes=No%20Permission!!!");
	exit();
}

//print "ParkedDomainOwnerClientID: ".$ParkedDomainOwnerClientID."<br>";
//print "ParkedDomainID: ".$_REQUEST["ParkedDomainID"]."<br>";
$parentDomainId = intVal($_REQUEST["parentDomainId"]);

if($oDomain->DeleteParkedDomain($ParkedDomainOwnerClientID, $_REQUEST["ParkedDomainID"], $Error) == 1) {
	$Notes="Parked Domain Deleted";
}
else
{	
	$Notes="Parked Domain cannot be deleted";
}


header("location: ./ListParkedDomains.php?DomainID=".$parentDomainId."&Notes=".$Notes.$Error);

