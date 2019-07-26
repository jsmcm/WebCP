<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oReseller = new Reseller();
$oUser = new User();
$oDomain = new Domain();
$oSimpleNonce = new SimpleNonce();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");


$Role = $oUser->Role;
$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

$DomainID = intVal($_POST["DomainID"]);
$DomainName = "";
$SubDomain = filter_var($_POST["SubDomain"], FILTER_SANITIZE_STRING);
$DomainOwnerClientID = $oDomain->GetDomainOwner($DomainID);

//print "Role: ".$Role."<br>";
//print "DomainOwnerClientID: ".$DomainOwnerClientID."<br>";
//print "client_id: ".$ClientID."<br>";

$nonceArray = [
	$oUser->Role,
	$oUser->getClientId(),
	$DomainID
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
$DomainName = $oDomain->GetDomainNameFromDomainID($DomainID, $nonce);
$AncestorDomainID = $oDomain->GetAncestorDomainID($DomainID);

//print "Domain Name: ".$DomainName."<br>";
//print "Domain ID: ".$DomainID."<br>";
//print "Sub Domain: ".$SubDomain."<br>";

$ResellerPermission = false;
if($oUser->Role == "reseller") {
	if($oReseller->GetClientResellerID($DomainOwnerClientID) == $ClientID) {	
		$ResellerPermission = true;
	}
}

if( ($ClientID != $DomainOwnerClientID) && ($Role != "admin")) {
	if($ResellerPermission == false) {
		header("location: ListSubDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Permission denied");
		exit();
	}
}


if(substr($SubDomain, 0, 7) == "http://") {
	$SubDomain = substr($SubDomain, 7);
}

if(substr($SubDomain, 0, 4) == "www.") {
	$SubDomain = substr($SubDomain, 4);
}


for($x = 0; $x < strlen($SubDomain); $x++) {
	if(!ctype_alnum($SubDomain[$x])) {
		if($SubDomain[$x] != '-') {
			header("location: ListSubDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Incorrectly formatted domain name");
			exit();
		}
		
	}
}



if($oDomain->SubDomainExists($SubDomain, $DomainID) > 0) {
	header("location: ListSubDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Sub Domain name already exists");
	exit();
}

$Error = "";
if($oDomain->AddSubDomain($SubDomain, $DomainID, $DomainOwnerClientID, $Error) < 1) {
	header("location: ListSubDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Cannot add sub domain");
	exit();
}
header("location: ListSubDomains.php?DomainID=".$AncestorDomainID."&NoteType=Success&Notes=Sub Domain added<br><b>Please wait 1 minute before adding email or FTP accounts for this sub domain</b>");


 
