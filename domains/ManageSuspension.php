<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDomain = new Domain();
$oSimpleNonce = new SimpleNonce();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}
	
if($oUser->Role == "client") {
	// Not an admin, get outta here
	header("Location: /index.php");
	exit();
}

if(! isset($_REQUEST["ChangeTo"])) {
	header("Location: index.php?Notes=Error, no state info found");
	exit();
}


if(! isset($_REQUEST["DomainID"])) {
	header("Location: index.php?Notes=Error, no domain ID found");
	exit();
}

$domainName = filter_var($_REQUEST["domainName"], FILTER_SANITIZE_STRING);
$domainId = intVal( $_REQUEST["DomainID"] );
$clientId = $oUser->ClientID;
$clientRole = $oUser->Role;

$timeStamp = filter_var($_REQUEST["timeStamp"], FILTER_SANITIZE_STRING);
$nonce = filter_var($_REQUEST["nonce"], FILTER_SANITIZE_STRING);

$nonceArray = [
	$domainName,
	$domainId,
	$clientRole,
	$clientId
];

$nonceResult = $oSimpleNonce->VerifyNonce($nonce, "suspendDomain", $timeStamp, $nonceArray);

if ( $nonceResult === false ) {
	header("Location: index.php?Notes=Security nonce failed&NoteType=error");
	exit();
}

if($_REQUEST["ChangeTo"] == 1) {
	if($oDomain->Suspend($domainId)) {
		header("Location: index.php?Notes=Account suspended successfully");
	} else {
		header("Location: index.php?Notes=Account suspension failed");
	}
} else {
	if($oDomain->Unsuspend($domainId)) {
		header("Location: index.php?Notes=Account unsuspended successfully");
	} else {
		header("Location: index.php?Notes=Account unsuspension failed");
	}
}
