<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oDomain = new Domain();
$oUser = new User();
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

$DomainID = filter_input(INPUT_POST, "DomainID", FILTER_SANITIZE_NUMBER_INT);
$DomainName = filter_input(INPUT_POST, "DomainName", FILTER_SANITIZE_STRING);

if($oDomain->DomainExists($DomainName) != $DomainID)
{
        header("Location: index.php?NoteType=error&Notes=There was a problem with your request, please try again (id:gscid!id)");
        exit();
}

$CountryCode = filter_input(INPUT_POST, "CountryCode", FILTER_SANITIZE_STRING);
$Province = filter_input(INPUT_POST, "Province", FILTER_SANITIZE_STRING);
$Town = filter_input(INPUT_POST, "Town", FILTER_SANITIZE_STRING);
$Organisation = filter_input(INPUT_POST, "Organisation", FILTER_SANITIZE_STRING);
$Division = filter_input(INPUT_POST, "Division", FILTER_SANITIZE_STRING);
$EmailAddress = filter_input(INPUT_POST, "EmailAddress", FILTER_SANITIZE_STRING);

$OutPut = "CountryCode=".$CountryCode."\n";
$OutPut = $OutPut."Province=".$Province."\n";
$OutPut = $OutPut."Town=".$Town."\n";
$OutPut = $OutPut."CompanyName=".$Organisation."\n";
$OutPut = $OutPut."Division=".$Division."\n";
$OutPut = $OutPut."EmailAddress=".$EmailAddress."\n";

file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$DomainName.".ssl", $OutPut);
sleep(6);
header("Location: index.php?NoteType=success&Notes=CSR generated for ".$DomainName.". If you don't see it in the list below please wait a few seconds then refresh the page. Once it appears here you can click on the green button to view the CSR");
