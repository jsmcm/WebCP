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
$keyId = intVal( $_REQUEST["keyId"] );
$nonceValue = filter_var( $_REQUEST["nonce"], FILTER_SANITIZE_STRING );
$nonceTimeStamp = filter_var( $_REQUEST["timeStamp"], FILTER_SANITIZE_STRING );


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
    $keyId,
    $domainId
];

$oSimpleNonce = new SimpleNonce();
$nonceResult = $oSimpleNonce->VerifyNonce($nonceValue, "deleteSSHKey", $nonceTimeStamp, $nonceMeta);

if ( ! $nonceResult ) {
    print "error: Nonce Failed";
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
    $oLog->WriteLog("error", "/deleteKey.php -> domainUserName not found");
    throw new Exception("<p><b>deleteKey.php -> domainUserName not found</b></p>");
}


$nonceArray = [	
    $oUser->Role,
    $oUser->ClientID,
    $keyId
];
$nonce = $oSimpleNonce->GenerateNonce("getFileName", $nonceArray);
$fileName = $oSSH->getFileName($keyId, $nonce);

$nonceArray = [	
    $oUser->Role,
    $oUser->ClientID,
    $domainId,
    $keyId
];
$nonce = $oSimpleNonce->GenerateNonce("deleteDomainPublicKey", $nonceArray);

if ($oSSH->deleteDomainPublicKey($domainId, $keyId, $nonce) == true ) {
    
    if ( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/".$domainId.".authorise_domain_pub_key") ) {
        touch($_SERVER["DOCUMENT_ROOT"]."/nm/".$domainId.".authorise_domain_pub_key");
    }

    if ( file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/".$keyId.".add_pub_key") ) {
        unlink($_SERVER["DOCUMENT_ROOT"]."/nm/".$keyId.".add_pub_key");
    }

    if ( file_exists("/home/".$domainUserName."/.ssh_hashes/".$fileName) ) {
        unlink("/home/".$domainUserName."/.ssh_hashes/".$fileName);
    }

    header("location: keys.php?domainId=".$domainId."&Notes=Success, key deleted&NoteType=success");
    exit();
}

header("location: keys.php?domainId=".$domainId."&Notes=Error, key could not be deleted&NoteType=error");
