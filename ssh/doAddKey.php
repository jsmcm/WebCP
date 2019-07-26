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
$domainName = filter_var( $_REQUEST["domainName"], FILTER_SANITIZE_STRING );
$nonceValue = filter_var( $_REQUEST["nonce"], FILTER_SANITIZE_STRING );
$nonceTimeStamp = filter_var( $_REQUEST["timeStamp"], FILTER_SANITIZE_STRING );
$keyName = filter_var( $_REQUEST["keyName"], FILTER_SANITIZE_STRING );
$publicKey = trim(filter_var( $_REQUEST["publicKey"], FILTER_SANITIZE_STRING ));



if ( substr($publicKey, 0, 24) !== "ssh-rsa AAAAB3NzaC1yc2EA" ) {
    header("Location: index.php?Notes=Invalid Pubic Key.&NoteType=Error");
    exit();
}


if ( strstr($_REQUEST["publicKey"], "<?") ) {
    header("Location: index.php?Notes=Invalid Pubic Key..&NoteType=Error");
    exit();
}

$keyTest = trim(substr($publicKey, 8));
$keyTest = trim(substr($keyTest, 0, strpos($keyTest, " ")));

if ( base64_encode(base64_decode($keyTest, true)) !== $keyTest){
    header("Location: index.php?Notes=Invalid Pubic Key...&NoteType=Error");
    exit();
}

$domainOwnerId = $oDomain->GetDomainOwner($domainId);
$resellerId = $oReseller->GetClientResellerID($domainOwnerId);

if ( $ClientID != $domainOwnerId ) {
	if ( $resellerId != $ClientID ) {
		header("Location: index.php?Notes=You don't have permission to edit that domain&NoteType=Error");
		exit();
	}
}


$nonceMeta = [
    $oUser->Role,
    $oUser->ClientID,
    $domainName,
    $domainId
];

$oSimpleNonce = new SimpleNonce();
$nonceResult = $oSimpleNonce->VerifyNonce($nonceValue, "doAddKey.php", $nonceTimeStamp, $nonceMeta);

if ( ! $nonceResult ) {
    header("Location: index.php?Notes=error: Nonce Failed&NoteType=Error");
    exit();
}

$nonceArray = [	
    $oUser->Role,
    $oUser->ClientID,
    $domainId,
    $keyName
];
$nonce = $oSimpleNonce->GenerateNonce("addDomainPublicKey", $nonceArray);

if ($oSSH->addDomainPublicKey($domainId, $keyName, $publicKey, $nonce) == true ) {

    header("location: keys.php?domainId=".$domainId."&Notes=Success, key added&NoteType=success");
    exit();
}

header("location: keys.php?domainId=".$domainId."&Notes=Error, key could not be added&NoteType=Error");



