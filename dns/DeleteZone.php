<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.DNS.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Log.php");


$oUser = new User();
$oDNS = new DNS();
$oLog = new Log();
$oDomain = new Domain();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

if($oUser->Role != "admin")
{
        header("Location: /index.php");
        exit();
}

	
$ZoneName = filter_var($_GET["ZoneName"], FILTER_SANITIZE_URL);
if($ZoneName != $_GET["ZoneName"])
{
	header("location: index.php?Notes=Invalid Domain Name&NoteType=error");
	exit();
}

$Role = $oUser->Role;


if($oDNS->DeleteZone($ZoneName) < 1)
{
	header("location: index.php?NoteType=Error&Notes=Cannot delete zone");
	exit();
}


header("location: index.php?NoteType=Message&Notes=Zone deleted<br>".$Error);

?>


