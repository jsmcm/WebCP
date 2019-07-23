<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDNS = new DNS();
$oLog = new Log();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$Role = $oUser->Role;

$oDNS->RecreateAllZoneInfo();

header("location: index.php?NoteType=Message&Notes=Zones recreated");
