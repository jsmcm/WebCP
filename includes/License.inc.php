<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUtils = new Utils();
$oDomains = new Domain();

$LicenseKey = "free";

if ( file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf")) {
    $LicenseKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf");
}

//print "count: ".$oDomains->GetAccountsCreatedCount()."<p>";
//print "LicenseKey: ".$LicenseKey."<p>";
$license = $oUtils->getLicense($LicenseKey);
//print "license: ".print_r($license, true)."<p>";


