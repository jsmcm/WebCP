<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oEmail = new Email();
$oSimpleNonce = new SimpleNonce();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");


$ClientID = $oUser->getClientId();
if($ClientID < 1) {
        header("Location: /index.php");
        exit();
}

if($oUser->Role != "admin")
{
	 header("Location: /index.php");
        exit();
}
	

$serviceName = filter_input(INPUT_POST, "serviceName", FILTER_SANITIZE_STRING);
$hostName = filter_input(INPUT_POST, "hostName", FILTER_SANITIZE_STRING);
$userName = filter_input(INPUT_POST, "userName", FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
$default = filter_input(INPUT_POST, "default", FILTER_SANITIZE_STRING);


$oEmail->deleteTransactionalEmailSettings();

if ( $userName != "" && $password != "" ) {
	$oEmail->saveTransactionalEmailSettings($serviceName, $hostName, $userName, $password, $default);
}

$random = random_int(1, 1000000);
$nonceArray = [
	$oUser->Role,
        $oUser->ClientID,
        $random
];
$nonce = $oSimpleNonce->GenerateNonce("makeTransactionalEmailEximSettings", $nonceArray);
$oEmail->makeTransactionalEmailEximSettings($random, $nonce);
header("Location: index.php?Notes=Saved!");

