<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oEmail = new Email();

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
	


$sendgridUserName = filter_input(INPUT_POST, "sendgridUserName", FILTER_SANITIZE_STRING);
$sendgridPassword = filter_input(INPUT_POST, "sendgridPassword", FILTER_SANITIZE_STRING);
$sendgridDefault = filter_input(INPUT_POST, "sendgridDefault", FILTER_SANITIZE_STRING);

$oEmail->deleteSendgridSettings();

if ( $sendgridUserName != "" && $sendgridPassword != "" ) {
	$oEmail->saveSendgridSettings($sendgridUserName, $sendgridPassword, $sendgridDefault);
}

$oEmail->makeSendgridEximSettings();
header("Location: index.php?Notes=Saved!");

