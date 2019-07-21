<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oSimpleNonce = new SimpleNonce();

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
        header("Location: /index.php");
        exit();
}

$Role = $oUser->Role;

$metaDataArray = [
	$ClientID,
	$Role
];

$nonce = $oSimpleNonce->GenerateNonce("backups/download", $metaDataArray);

$nonce = filter_var($_GET["nonce"], FILTER_SANITIZE_STRING);
$timeStamp = filter_var($_GET["timeStamp"], FILTER_SANITIZE_STRING);

$nonceResult = $oSimpleNonce->VerifyNonce($nonce, "backups/download", $timeStamp, $metaDataArray);

if ( $nonceResult === false ) {
	header("Location: index.php?Notes=Nonce failed. You can only use a download link once and for a limited time. To download the file again please refresh the page and try again&NoteType=Error");
	exit();
}

if(! isset($_GET["backupFile"])) {
	print "No file supplied!";
	exit();
}

$file = $_GET["backupFile"];
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename=".basename($file));
header("Content-Transfer-Encoding: binary");
readfile($file);
