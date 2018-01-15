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


$ServerType = "no_dns";
if(isset($_POST["ServerType"]))
{
	$ServerType = filter_var($_POST["ServerType"], FILTER_SANITIZE_STRING);
}

$TTL = "";
if(isset($_POST["TTL"]))
{
	$TTL = filter_var($_POST["TTL"], FILTER_SANITIZE_NUMBER_INT);
	if($TTL < 0)
	{
		$TTL = 7200;
	}
	$oDNS->SaveSetting("ttl", $TTL);
}

$NegativeTTL = "";
if(isset($_POST["NegativeTTL"]))
{
	$NegativeTTL = filter_var($_POST["NegativeTTL"], FILTER_SANITIZE_NUMBER_INT);
	if($NegativeTTL < 0)
	{
		$NegativeTTL = 7200;
	}
	$oDNS->SaveSetting("negative_ttl", $NegativeTTL);
}

$Refresh = "";
if(isset($_POST["Refresh"]))
{
	$Refresh = filter_var($_POST["Refresh"], FILTER_SANITIZE_NUMBER_INT);
	if($Refresh < 0)
	{
		$Refresh = 7200;
	}
	$oDNS->SaveSetting("refresh", $Refresh);
}

$Retry = "";
if(isset($_POST["Retry"]))
{
	$Retry = filter_var($_POST["Retry"], FILTER_SANITIZE_NUMBER_INT);
	if($Retry < 0)
	{
		$Retry = 1800;
	}
	$oDNS->SaveSetting("retry", $Retry);
}

$Expire = "";
if(isset($_POST["Expire"]))
{
	$Expire = filter_var($_POST["Expire"], FILTER_SANITIZE_NUMBER_INT);
	if($Expire < 0)
	{
		$Expire = 1209600;
	}
	$oDNS->SaveSetting("expire", $Expire);
}

$EmailAddress = "";
if(isset($_POST["EmailAddress"]))
{
	$EmailAddress = filter_var($_POST["EmailAddress"], FILTER_SANITIZE_EMAIL);

	if($EmailAddress != "")
	{
		$oDNS->SaveSetting("email_address", $EmailAddress);
	}
}

$PrimaryNameServer = "";
if(isset($_POST["PrimaryNameServer"]))
{
	$PrimaryNameServer = filter_var($_POST["PrimaryNameServer"], FILTER_SANITIZE_STRING);

	if($PrimaryNameServer != "")
	{
		$oDNS->SaveSetting("primary_name_server", $PrimaryNameServer);
	}
}

$Password = "";
if(isset($_POST["Password"]))
{
	$Password = filter_var($_POST["Password"], FILTER_SANITIZE_STRING);

	if($Password != "")
	{
		$oDNS->SaveSetting("password", $Password);
	}
}

$MasterPassword = "";
if(isset($_POST["MasterPassword"]))
{
	$MasterPassword = filter_var($_POST["MasterPassword"], FILTER_SANITIZE_STRING);

	if($MasterPassword != "")
	{
		$oDNS->SaveSetting("master_password", $MasterPassword);
	}
}

$MasterPublicKey = "";
if(isset($_POST["MasterPublicKey"]))
{
	$MasterPublicKey = filter_var($_POST["MasterPublicKey"], FILTER_SANITIZE_STRING);

	if($MasterPublicKey != "")
	{
		$oDNS->SaveSetting("master_public_key", $MasterPublicKey);
	}
}

$MasterHostName = "";
if(isset($_POST["MasterHostName"]))
{
	$MasterHostName = filter_var($_POST["MasterHostName"], FILTER_SANITIZE_STRING);

	if($MasterHostName != "")
	{
		$oDNS->SaveSetting("master_host_name", $MasterHostName);
	}
}


$MasterIPAddress = "";
if(isset($_POST["MasterIPAddress"]))
{
	$MasterIPAddress = filter_var($_POST["MasterIPAddress"], FILTER_SANITIZE_STRING);

	if($MasterIPAddress != "")
	{
		$oDNS->SaveSetting("master_ip_address", $MasterIPAddress);
	}
}

$oDNS->SaveSetting("server_type", $ServerType);

header("location: settings.php?Notes=Settings saved!");

?>
