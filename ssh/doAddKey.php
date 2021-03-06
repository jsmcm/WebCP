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
$domainName = filter_var( $_REQUEST["domainName"], FILTER_SANITIZE_STRING );
$nonceValue = filter_var( $_REQUEST["nonce"], FILTER_SANITIZE_STRING );
$nonceTimeStamp = filter_var( $_REQUEST["timeStamp"], FILTER_SANITIZE_STRING );
$keyName = filter_var( $_REQUEST["keyName"], FILTER_SANITIZE_STRING );
$publicKey = trim(filter_var( $_REQUEST["publicKey"], FILTER_SANITIZE_STRING ));



if ( substr($publicKey, 0, 24) !== "ssh-rsa AAAAB3NzaC1yc2EA" ) {
    sleep(1);
    header("Location: keys.php?domainId=".$domainId."&Notes=Invalid Pubic Key.&NoteType=Error");
    exit();
}


if ( strstr($_REQUEST["publicKey"], "<?") ) {
    sleep(1);
    header("Location: keys.php?domainId=".$domainId."&Notes=Invalid Pubic Key..&NoteType=Error");
    exit();
}

$keyTest = trim(substr($publicKey, 8));
$keyTest = trim(substr($keyTest, 0, strpos($keyTest, " ")));

if ( base64_encode(base64_decode($keyTest, true)) !== $keyTest){
    sleep(1);
    header("Location: keys.php?domainId=".$domainId."&Notes=Invalid Pubic Key...&NoteType=Error");
    exit();
}

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
    $oUser->ClientID,
    $domainOwnerId,
    $random
];

$oReseller = new Reseller();
$nonce = $oSimpleNonce->GenerateNonce("getClientResellerID", $nonceArray);
$resellerId = $oReseller->GetClientResellerID($domainOwnerId, $random, $nonce);

if ( $ClientID != $domainOwnerId ) {
	if ( $resellerId != $ClientID ) {

		if ($oUser->Role != "admin") {
			sleep(1);
			header("Location: keys.php?domainId=".$domainId."&Notes=You don't have permission to edit that domain&NoteType=Error");
			exit();
		}
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
    sleep(1);
    header("Location: keys.php?domainId=".$domainId."&Notes=error: Nonce Failed&NoteType=Error");
    exit();
}


$oDomain = new Domain();

$nonceArray = [	
    $oUser->Role,
    $oUser->ClientID,
    $domainId
];
$nonce = $oSimpleNonce->GenerateNonce("getDomainUserName", $nonceArray);		
$domainUserName = $oDomain->getDomainUserName( $domainId, $nonce);

if ( $domainUserName == "" ) {
    $oLog = new Log();
    $oLog->WriteLog("error", "/doAddKey.php -> domainUserName not found");
    throw new Exception("<p><b>doAddKey.php -> domainUserName not found</b></p>");
}



$nonceArray = [	
    $oUser->Role,
    $oUser->ClientID,
    $domainId,
    $keyName,
    $domainUserName
];
$nonce = $oSimpleNonce->GenerateNonce("checkForDuplicateDomainPublicKey", $nonceArray);

$duplicateKeyName = $oSSH->checkForDuplicateDomainPublicKey($domainId, $keyName, $publicKey, $domainUserName, $nonce);
if ($duplicateKeyName != "" ) {
    sleep(1);
    header("Location: keys.php?domainId=".$domainId."&Notes=error: An identical key already exists for this domain (".$duplicateKeyName.")&NoteType=Error");
    exit();
}




$nonceArray = [	
    $oUser->Role,
    $oUser->ClientID,
    $domainId,
    $keyName
];
$nonce = $oSimpleNonce->GenerateNonce("checkForDuplicateFileName", $nonceArray);

$duplicateKeyName = $oSSH->checkForDuplicateFileName($domainId, $keyName, $nonce);
if ($duplicateKeyName == true ) {
    sleep(1);
    header("Location: keys.php?domainId=".$domainId."&Notes=error: That key name conflicts with an existing key name, please try again with a different key name&NoteType=Error");
    exit();
}



$nonceArray = [	
    $oUser->Role,
    $ClientID,
    $domainId,
    $keyName,
    $domainUserName
];
$nonce = $oSimpleNonce->GenerateNonce("addDomainPublicKey", $nonceArray);

if ($oSSH->addDomainPublicKey($domainId, $keyName, $publicKey, $domainUserName, $nonce) == true ) {

    sleep(1);
    header("location: keys.php?domainId=".$domainId."&Notes=Success, key added&NoteType=success");
    exit();
}

sleep(1);
header("location: keys.php?domainId=".$domainId."&Notes=Error, key could not be added&NoteType=Error");



