<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oEmail = new Email();
$oSimpleNonce = new SimpleNonce();


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
	

$transactional = filter_input(INPUT_POST, "transactional", FILTER_SANITIZE_STRING);
$domainId = filter_input(INPUT_POST, "domain_id", FILTER_SANITIZE_STRING);

$oEmail->saveTransactionalDomain($transactional, $domainId);

$random = random_int(1, 1000000);
$nonceArray = [
	$oUser->Role,
        $oUser->ClientID,
        $random
];
$nonce = $oSimpleNonce->GenerateNonce("makeTransactionalEmailEximSettings", $nonceArray);
$oEmail->makeTransactionalEmailEximSettings($random, $nonce);
