<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUtils = new Utils();
$oDomains = new Domain();

//$license = $oUtils->getLicense($LicenseKey);

// open source now, just give access...
// This is the quickest way of "removing" the license requirement until the checks have been removed from all the sites pages.
$license = new stdClass();
$license->success = "sucess";
$license->type = "free";
$license->allowed = 1000000000;
$license->item_id = 0;
$license->item_name = "WebCP";
$license->checksum = "";


