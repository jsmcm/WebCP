<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();

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


if ($ClientID != $oDomain->GetDomainOwner($domainId) && ($Role == 'client')) {
    print "error: Permission denied";
    exit();
}

if ($oDomain->saveDomainSetting($domainId, "parked_redirect", $redirect, "", "")) {
    touch($_SERVER["DOCUMENT_ROOT"]."/nm/".$domainId.".subdomain", 0755);
    print "Parked Redirect Saved";
    exit();
}


print "error: Unknown error";
