<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oEmail = new Email();
$oSimpleNonce = new SimpleNonce();
$oDomain = new Domain();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
    header("Location: /index.php");
    exit();
}

$domainId = intVal($_REQUEST["DomainID"]);


$nonceArray = [
    $oUser->Role,
    $ClientID,
    $domainId
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
$DomainOwnerClientID = $oDomain->GetDomainOwner($domainId, $nonce);

if( ($ClientID != $DomainOwnerClientID) && ($oUser->Role != 'admin') ) {	
    header("location: /domains/index.phpNotes=No%20Permission!!!&NoteType=Error");
    exit();
}

if ( $oUser->Role == "client" ) {
    header("location: /domains/index.php?Notes=Sorry, you can't delete domains!!!&NoteType=Error");
    exit(); 
}
//print "DomainOwnerClientID: ".$DomainOwnerClientID."<br>";
//print "DomainID: ".$_REQUEST["DomainID"]."<br>";

$domainName = filter_var($_REQUEST["domainName"], FILTER_SANITIZE_STRING);
$clientId = $ClientID;
$clientRole = $oUser->Role;

$timeStamp = filter_var($_REQUEST["timeStamp"], FILTER_SANITIZE_STRING);
$nonce = filter_var($_REQUEST["nonce"], FILTER_SANITIZE_STRING);

$nonceArray = [
    $domainName,
    $domainId,
    $clientRole,
    $clientId
];

$nonceResult = $oSimpleNonce->VerifyNonce($nonce, "deleteDomain", $timeStamp, $nonceArray);
        
if ( $nonceResult === false ) {
    header("Location: index.php?Notes=Security nonce failed&NoteType=error");
    exit();
}


$nonceArray = [
    $oUser->Role,
    $ClientID,
    $DomainOwnerClientID,
    $domainId
];

$nonce = $oSimpleNonce->GenerateNonce("deleteDomain", $nonceArray);
if($oDomain->DeleteDomain($DomainOwnerClientID, $domainId, $Error, $nonce) == 1) {

    $random = random_int(1,100000);
    $nonceArray = [
        $oUser->Role,
        $ClientID,
        $random
    ];
    
    $nonce = $oSimpleNonce->GenerateNonce("makeSendgridEximSettings", $nonceArray);
	$oEmail->makeSendgridEximSettings($random, $nonce);
	$Notes="Domain Deleted";
} else {	
	$Notes="Domain cannot be deleted";
}


header("location: index.php?Notes=".$Notes.$Error);	
