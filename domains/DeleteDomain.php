<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oEmail = new Email();
$oSimpleNonce = new SimpleNonce();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
    header("Location: /index.php");
    exit();
}

$oDomain = new Domain();
$DomainOwnerClientID = $oDomain->GetDomainOwner($_REQUEST["DomainID"]);

if( ($ClientID != $DomainOwnerClientID) && ($oUser->Role != 'admin') ) {	
    header("location: index?Notes=No%20Permission!!!");
    exit();
}

//print "DomainOwnerClientID: ".$DomainOwnerClientID."<br>";
//print "DomainID: ".$_REQUEST["DomainID"]."<br>";

$domainName = filter_var($_REQUEST["domainName"], FILTER_SANITIZE_STRING);
$domainId = intVal( $_REQUEST["DomainID"] );
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



if($oDomain->DeleteDomain($DomainOwnerClientID, $domainId, $Error) == 1) {
	$oEmail->makeSendgridEximSettings();
	$Notes="Domain Deleted";
} else {	
	$Notes="Domain cannot be deleted";
}


header("location: index.php?Notes=".$Notes.$Error);	
