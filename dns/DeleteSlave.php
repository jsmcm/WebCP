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



if(isset($_POST["ID"]))
{
	$oDNS->DeleteSlave(intVal($_POST["ID"]));
}

header("location: slaves.php?Notes=Slave Deleted!");
