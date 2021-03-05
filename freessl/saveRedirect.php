<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();
$oSimpleNonce = new SimpleNonce();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$Role = $oUser->Role;

$domainId = 0;
if (isset($_POST["domain_id"])) {
    $domainId = intVal($_POST["domain_id"]);
} else {
    print "error: Domain not set";
    exit();
}

$redirect = "none";
if ( isset($_POST["redirect"])) {
    $redirect = filter_var($_POST["redirect"], FILTER_SANITIZE_STRING);
}

$random = random_int(1, 100000);
$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
    $domainId,
    $random
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
if ($ClientID != $oDomain->GetDomainOwner($domainId, $random, $nonce) && ($Role == 'client')) {
    print "error: Permission denied";
    exit();
}

$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
    $domainId,
    "ssl_redirect",
    $redirect
];

$nonce = $oSimpleNonce->GenerateNonce("saveDomainSetting", $nonceArray);
if ($oDomain->saveDomainSetting($domainId, "ssl_redirect", $redirect, "", "", $nonce)) {
    touch($_SERVER["DOCUMENT_ROOT"]."/nm/".$domainId.".subdomain", 0755);
    print "SSL Redirect Saved";
    exit();
}


print "error: Unknown error";
