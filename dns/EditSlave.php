<?php

session_start();

function __autoload($classname)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

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



if(isset($_POST["HostName"]))
{
	$NewHostName = $_POST["HostName"];
	$NewIPAddress = filter_var($_POST["IPAddress"], FILTER_VALIDATE_IP);
	$NewPublicKey = filter_var($_POST["PublicKey"], FILTER_SANITIZE_STRING);
	$NewPassword = filter_var($_POST["Password"], FILTER_SANITIZE_STRING);
	$ID = intVal($_POST["ID"]);
	
	if( ($NewPublicKey == "") || (($NewHostName == "") && ($NewIPAddress == "")) )
	{
		;	
	}
	else
	{
	
		if($NewIPAddress != $_POST["IPAddress"])
		{
			header("Location: slaves.php?Notes=Invalid IP Address&NoteType=Error");
			exit();
		}

		if($NewHostName != $_POST["HostName"])
		{
			header("Location: slaves.php?Notes=Invalid Host Name&NoteType=Error");
			exit();
		}
		
		if($NewPassword != $_POST["Password"])
		{
			header("Location: slaves.php?Notes=Invalid Password&NoteType=Error");
			exit();
		}
		
		if($NewPublicKey != $_POST["PublicKey"])
		{
			header("Location: slaves.php?Notes=Invalid Public Key&NoteType=Error");
			exit();
		}

		$oDNS->EditSlave($ID, $NewHostName, $NewIPAddress, $NewPublicKey, $NewPassword);
	}
}

header("location: slaves.php?Notes=Slave Saved!");

?>
