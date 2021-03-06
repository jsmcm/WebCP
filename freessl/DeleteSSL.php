<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();

$oSettings = new Settings();


if($oUser->Role != "admin")
{
	header("Location: /index.php");
	exit();
}

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}

$DomainID = filter_input(INPUT_GET, "DomainID", FILTER_SANITIZE_NUMBER_INT);
$PrimaryDomainID = filter_input(INPUT_GET, "PrimaryDomainID", FILTER_SANITIZE_NUMBER_INT);
$Domain = filter_input(INPUT_GET, "Domain", FILTER_SANITIZE_STRING);
$Type = filter_input(INPUT_GET, "Type", FILTER_SANITIZE_STRING);

if($oDomain->DomainExists($Domain) != $DomainID)
{
	header("Location: index.php?NoteType=error&Notes=There was a problem with your request, please try again (id:dscid!id)");
	exit();
}



    $oSimpleNonce = new SimpleNonce();
    $nonceArray = [
            $oUser->Role,
            $oUser->ClientID,
	    $DomainID,
	    "letsencrypt"
    ];
    $nonce = $oSimpleNonce->GenerateNonce("deleteDomainSetting", $nonceArray);

$oDomain->deleteDomainSetting($DomainID, "letsencrypt", $nonce);

file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$Domain.".deletefreessl", $PrimaryDomainID);
sleep(2);
header("Location: index.php?NoteType=success&Notes=SSL scheduled for deletion, it could take a few minutes. Refresh the page in a short while if the domain still appears in the list.");

