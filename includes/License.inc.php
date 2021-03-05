<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUtils = new Utils();
$oDomains = new Domain();

$LicenseKey = "free";

if ( file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf")) {
    $LicenseKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf");
}

//print "count: ".$oDomains->GetAccountsCreatedCount()."<p>";
///print "LicenseKey: ".$LicenseKey."<p>";
$key = $oUtils->getValidationKey($LicenseKey);

//print "key: ".$key."<p>";


if( $key == "expired" ) {
    header("location: /index.php?Notes=License is expired. Please renew or contact support: <a href=\"https://webcp.io\">webcp.io</a>");
    exit();
} else if ($key == "not-found" ) {
    header("location: /index.php?Notes=License not found. Please register for one at: <a href=\"https://webcp.io\">webcp.io</a><p><a href=\"/enter_license.php\">Enter License Key</a>");
    exit();
}


$validationData = $oUtils->getValidationData($key);

//print "validattionData: ".print_r($validationData, true)."<p>";

$validationArray = json_decode($validationData, true);

//print "validationArray: ".print_r($validationArray, true)."<p>";

if ($validationArray["type"] != "free") {
    if ( ($oUtils->ValidateHash($validationArray["hash"], $LicenseKey) !== true) || $validationArray["status"] != "valid" ) {
        header("location: /index.php?Notes=License failed, please try logging in again or contact support");
        exit();
    }
} else if ($validationArray["status"] != "valid") {
    header("location: /index.php?Notes=License failed at pos: lip2, please try logging in again or contact support");
    exit();
}

