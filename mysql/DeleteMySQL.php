<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$oMySQL = new MySQL();
if($oMySQL->DeleteMySQL($ClientID, $oUser->Role, $_REQUEST["id"]) == 1)
{
	$Notes="Database deleted";
}
else
{	
	$Notes="Database cannot be deleted";
}

//print $Notes;
//exit();

if(isset($_REQUEST["ClientID"]))
{
	header("location: index.php?Notes=".$Notes."&ClientID=".$_REQUEST["ClientID"]);	
}
else
{
	header("location: index.php?Notes=".$Notes);	
}
exit();

