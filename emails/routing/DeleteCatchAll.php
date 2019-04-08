<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oEmail = new Email();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}

$Role = $oUser->Role;

$DomainName = "";
if(isset($_REQUEST["DomainName"]))
{
	$DomainName = $_REQUEST["DomainName"];
}
else
{
	header("location: index.php?Notes=Catch All Not Deleted&NoteType=error");
	exit();
}

$oEmail->DeleteCatchAll($ClientID, $Role, $DomainName);

header("location: index.php?Notes=Catch All Deleted&NoteType=success");

?>
