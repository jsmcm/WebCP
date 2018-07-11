<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Utils.php");
$oUtils = new Utils();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.DNS.php");
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

?>
