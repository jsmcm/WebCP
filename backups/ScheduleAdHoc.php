<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDomain = new Domain();


$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}

$Role = $oUser->Role;

if($Role != "admin")
{
	if($_POST["ClientID"] != $ClientID)
	{
		header("location: index.php?Notes=Incorrect permissions, please contact support");
		exit();
	}	
}	

$EmailString = "";
if($_POST["EmailAddress"] != "")
{
	$EmailString = "&EmailAddress=".$_POST["EmailAddress"];
}

header("location: scripts/db.php?DomainID=".$_POST["DomainID"]."&Type=adhoc&RandomString=".date("Y-m-d_H-i-s")."&ReturnURL=../index.php&Notes=Backup scheduled!<p><b>Please wait a while for the scheduler to run until it shows up in the list. You will need to refresh this page to see when it appears</b>".$EmailString);



?>
