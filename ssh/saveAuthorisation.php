<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();
$oSimpleNonce = new SimpleNonce();
$oReseller = new Reseller();
$oSSH = new SSH();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

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


$domainOwnerId = $oDomain->GetDomainOwner($domainId);
$resellerId = $oReseller->GetClientResellerID($domainOwnerId);

if ( $ClientID != $domainOwnerId ) {
	if ( $resellerId != $ClientID ) {
		header("Location: index.php?Notes=You don't have permission to edit that domain&NoteType=error");
		exit();
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
    print "success: key ".(($authorisation==0)?"un":"")."authorised";
    exit();
}

print "Error: unknown error occured";



