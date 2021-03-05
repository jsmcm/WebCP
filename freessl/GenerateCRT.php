<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oDomain = new Domain();
$oUser = new User();
$oSettings = new Settings();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");
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
$DomainName = filter_input(INPUT_GET, "DomainName", FILTER_SANITIZE_STRING);
$DomainUserName = filter_input(INPUT_GET, "DomainUserName", FILTER_SANITIZE_STRING);
$Path = filter_input(INPUT_GET, "Path", FILTER_SANITIZE_STRING);
$Type = filter_input(INPUT_GET, "Type", FILTER_SANITIZE_STRING);

if($oDomain->DomainExists($DomainName) != $DomainID)
{
        header("Location: index.php?NoteType=error&Notes=There was a problem with your request, please try again (id:icid!id)");
        exit();
}

file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$DomainName.".freessl", "PrimaryDomainID=".$PrimaryDomainID."\nType=".$Type."\nPath=".$Path."\nDomainID=".$DomainID."\nDomainName=".$DomainName."\nDomainUserName=".$DomainUserName."\nEmailAddress=".$oUser->EmailAddress."\n");

header("Location: index.php?NoteType=success&Notes=Certificate ordered. It may take up to 5 minutes appear here. Once it appears here it means that SSL is working");

