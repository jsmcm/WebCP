<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oSettings = new Settings();
$oUtils = new Utils();
$oDomain = new Domain();
$oSimpleNonce = new SimpleNonce();
$oReseller = new Reseller();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

$domainName = filter_var($_REQUEST["domainName"], FILTER_SANITIZE_STRING);
$domainId = intVal( $_REQUEST["domainId"] );
$clientId = $ClientID;
$clientRole = $oUser->Role;

$random = random_int(1, 100000);
$nonceArray = [
    $oUser->Role,
    $ClientID,
	$domainId,
	$random
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
$domainOwnerId = $oDomain->GetDomainOwner($domainId, $random, $nonce);

$nonceArray = [
    $oUser->Role,
    $ClientID,
    $domainOwnerId
];

$nonce = $oSimpleNonce->GenerateNonce("getClientResellerID", $nonceArray);
$resellerId = $oReseller->GetClientResellerID($domainOwnerId, $nonce);

if ( $oUser->Role != "admin" ) {
	if ( $clientId != $domainOwnerId ) {
		if ( $resellerId != $clientId ) {
			header("Location: index.php?Notes=You don't have permission to edit that domain&NoteType=error");
			exit();
		}
	}
}

$timeStamp = filter_var($_REQUEST["timeStamp"], FILTER_SANITIZE_STRING);
$nonce = filter_var($_REQUEST["nonce"], FILTER_SANITIZE_STRING);

$nonceArray = [
        $domainName,
        $domainId,
        $clientRole,
	$clientId,
	"sslRedirect",
	"domainRedirect",
	"phpVersion"
];

$nonceResult = $oSimpleNonce->VerifyNonce($nonce, "domainSettings", $timeStamp, $nonceArray);

if ($nonceResult === false) {
	header("Location: index.php?Notes=Security nonce failed&NoteType=error");
	exit();
}

$domainRedirect = filter_var($_POST["domainRedirect"], FILTER_SANITIZE_STRING);
$nonceArray = [
    $oUser->Role,
    $ClientID,
	$domainId,
	"domain_redirect",
	$domainRedirect
];

$nonce = $oSimpleNonce->GenerateNonce("saveDomainSetting", $nonceArray);
$oDomain->saveDomainSetting($domainId, "domain_redirect", $domainRedirect, "", "", $nonce);

$sslRedirect = filter_var($_POST["sslRedirect"], FILTER_SANITIZE_STRING);
$nonceArray = [
    $oUser->Role,
    $ClientID,
	$domainId,
	"ssl_redirect",
	$sslRedirect
];

$nonce = $oSimpleNonce->GenerateNonce("saveDomainSetting", $nonceArray);
$oDomain->saveDomainSetting($domainId, "ssl_redirect", $sslRedirect, "", "", $nonce);

$phpVersion = filter_var($_POST["phpVersion"], FILTER_SANITIZE_STRING);
$nonceArray = [
    $oUser->Role,
    $ClientID,
	$domainId,
	"php_version",
	$phpVersion
];

$nonce = $oSimpleNonce->GenerateNonce("saveDomainSetting", $nonceArray);
$oDomain->saveDomainSetting($domainId, "php_version", $phpVersion, "", "", $nonce);


touch($_SERVER["DOCUMENT_ROOT"]."/nm/".$domainId.".subdomain", 0755);

header("Location: index.php?Notes=Domain Settings Saved");
