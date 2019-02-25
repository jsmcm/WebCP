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
	
if($oUser->Role == "client")
{
	// Not an admin, get outta here
	header("Location: /index.php");
	exit();
}

if(! isset($_REQUEST["ChangeTo"]))
{
	header("Location: index.php?Notes=Error, no state info found");
	exit();
}


if(! isset($_REQUEST["DomainID"]))
{
	header("Location: index.php?Notes=Error, no domain ID found");
	exit();
}

if($_REQUEST["ChangeTo"] == 1)
{
	if($oDomain->Suspend($_REQUEST["DomainID"]))
	{
		header("Location: index.php?Notes=Account suspended successfully");
	}
	else
	{
		header("Location: index.php?Notes=Account suspension failed");
	}
}
else
{
	if($oDomain->Unsuspend($_REQUEST["DomainID"]))
	{
		header("Location: index.php?Notes=Account unsuspended successfully");
	}
	else
	{
		header("Location: index.php?Notes=Account unsuspension failed");
	}
}
