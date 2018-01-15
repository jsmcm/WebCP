<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.DNS.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Log.php");


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

header("location: index.php?NoteType=Message&Notes=Zones recreated<br>".$Error);

?>


