<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.FTP.php");
$oFTP = new FTP();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Reseller.php");
$oReseller = new Reseller();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$ID = intVal($_POST["id"]);
$Password = $_POST["Password"];

$Role = $oUser->Role;
$FTPOwnerID = $oFTP->GetFTPOwner($ID);


$ResellerID = 0;
if($oUser->Role == "reseller")
{
        $ResellerID = $oReseller->GetClientResellerID($FTPOwnerID);
}

if( ($FTPOwnerID != $ClientID) && ($ResellerID != $ClientID) && ($oUser->Role != "admin") )
{
        header("location: index.php?NoteType=Error&Notes=You do not have permission to do that!");
        exit();
}

if($oFTP->EditFTPPassword($ID, $Password) < 1)
{
	header("location: index.php?NoteType=Error&Notes=Cannot change password");
	exit();
}

header("location: index.php?NoteType=Success&Notes=Password changed");

?>


