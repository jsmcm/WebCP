<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();
$oSimpleNonce = new SimpleNonce();
$oReseller = new Reseller();
$oSSH = new SSH();


$ClientID = $oUser->getClientId();
if($ClientID < 1) {
    header("Location: /index.php");
    exit();
}

$Role = $oUser->Role;
     
$domainId = intVal( $_REQUEST["domainId"] );
$authorisation = intVal( $_REQUEST["authorisation"] );
$publicKeyId = intVal( $_REQUEST["publicKeyId"] );
$nonceValue = filter_var( $_REQUEST["nonceValue"], FILTER_SANITIZE_STRING );
$nonceTimeStamp = filter_var( $_REQUEST["nonceTimeStamp"], FILTER_SANITIZE_STRING );

$random = random_int(1, 100000);
$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
    $domainId,
    $random
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
$domainOwnerId = $oDomain->GetDomainOwner($domainId, $random, $nonce);

$random = random_int(1, 100000);
$nonceArray = [
	$oUser->Role,
	$oUser->getClientId(),
	$domainOwnerId,
	$random
];

$oSimpleNonce = new SimpleNonce();

$nonce = $oSimpleNonce->GenerateNonce("getClientResellerID", $nonceArray);
$resellerId = $oReseller->GetClientResellerID($domainOwnerId, $random, $nonce);

if ( $ClientID != $domainOwnerId ) {
	if ( $resellerId != $ClientID ) {
		
		if ($oUser->Role != "admin") {
			header("Location: index.php?Notes=You don't have permission to edit that domain&NoteType=error");
			exit();
		}

	}
}


$nonceMeta = [
    $oUser->Role,
    $oUser->ClientID,
    $publicKeyId,
    $domainId
];

$oSimpleNonce = new SimpleNonce();
$nonceResult = $oSimpleNonce->VerifyNonce($nonceValue, "pubKeyAuthorisation", $nonceTimeStamp, $nonceMeta);

if ( ! $nonceResult ) {
    print "error: Nonce Failed";
    exit();
}

$nonceArray = [	
    $oUser->Role,
    $oUser->ClientID,
    $publicKeyId,
    $domainId,
    $authorisation
];
$nonce = $oSimpleNonce->GenerateNonce("changeKeyAuthorisation", $nonceArray);

if ($oSSH->changeKeyAuthorisation($publicKeyId, $domainId, $authorisation, $nonce) == true ) {
    touch($_SERVER["DOCUMENT_ROOT"]."/nm/".$domainId.".authorise_domain_pub_key");    
    print "success: key ".(($authorisation==0)?"un":"")."authorised";
    exit();
}

print "Error: unknown error occured";



