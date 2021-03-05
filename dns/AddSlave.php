<?php

session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oUtils = new Utils();
$oDNS = new DNS();

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



if(isset($_POST["HostName_New"]))
{
	$NewHostName = $_POST["HostName_New"];
	$NewIPAddress = filter_var($_POST["IPAddress_New"], FILTER_VALIDATE_IP);
	$NewPublicKey = filter_var($_POST["PublicKey_New"], FILTER_SANITIZE_STRING);
	$NewPassword = filter_var($_POST["Password_New"], FILTER_SANITIZE_STRING);

	if( ($NewPublicKey == "") || ($NewPassword == "") || (($NewHostName == "") && ($NewIPAddress == "")) )
	{
		;	
	}
	else
	{
		$oDNS->AddSlave($NewHostName, $NewIPAddress, $NewPublicKey, $NewPassword);
	}
}

header("location: slaves.php?Notes=New Slave Saved!");
