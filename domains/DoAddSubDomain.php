<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oReseller = new Reseller();
$oUser = new User();
$oDomain = new Domain();
$oSimpleNonce = new SimpleNonce();



$Role = $oUser->Role;
$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

$DomainID = intVal($_POST["DomainID"]);
$SubDomain = filter_var($_POST["SubDomain"], FILTER_SANITIZE_STRING);

$random = random_int(1, 100000);
$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
	$DomainID,
	$random
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
$DomainOwnerClientID = $oDomain->GetDomainOwner($DomainID, $random, $nonce);

//print "Role: ".$Role."<br>";
//print "DomainOwnerClientID: ".$DomainOwnerClientID."<br>";
//print "client_id: ".$ClientID."<br>";

$AncestorDomainID = $oDomain->GetAncestorDomainID($DomainID);

//print "Domain ID: ".$DomainID."<br>";
//print "Sub Domain: ".$SubDomain."<br>";

$ResellerPermission = false;
if($oUser->Role == "reseller") {

	$random = random_int(1, 100000);
	$nonceArray = [
		$oUser->Role,
		$ClientID,
		$DomainOwnerClientID,
		$random
	];

	$nonce = $oSimpleNonce->GenerateNonce("getClientResellerID", $nonceArray);
	$ResellerID = $oReseller->GetClientResellerID($DomainOwnerClientID, $random, $nonce);

	if($ResellerID == $ClientID) {	
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
$subDomainId = $oDomain->AddSubDomain($SubDomain, $DomainID, $DomainOwnerClientID, $Error);
if($subDomainId < 1) {

	header("location: ListSubDomains.php?DomainID=".$AncestorDomainID."&NoteType=Error&Notes=Cannot add sub domain");
	exit();

} 


// success
$infoArray = array();


$random = random_int(1, 1000000);
$oUser = new User();
$oSimpleNonce = new SimpleNonce();
$nonceArray = [	
	$oUser->Role,
	$oUser->ClientID,
	$AncestorDomainID,
	$random
];
$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
$oDomain->GetDomainInfo($AncestorDomainID, $random, $infoArray, $nonce);

file_put_contents(dirname(__DIR__)."/nm/".$SubDomain.".".$infoArray["DomainName"].".freessl_tmp", "PrimaryDomainID=".$AncestorDomainID."\nType=subdomain\nPath=".$infoArray["Path"]."/".strtolower($SubDomain)."\nDomainID=".$subDomainId."\nDomainName=".$SubDomain.".".$infoArray["DomainName"]."\nDomainUserName=".$infoArray["UserName"]."\nEmailAddress=".$oUser->EmailAddress."\n");



header("location: ListSubDomains.php?DomainID=".$AncestorDomainID."&NoteType=Success&Notes=Sub Domain added<br><b>Please wait 1 minute before adding email or FTP accounts for this sub domain</b>");


 
