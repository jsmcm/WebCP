<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.FTP.php");
$oFTP = new FTP();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$ID = $_POST["id"];
$Password = $_POST["Password"];

$Role = $oUser->Role;



if($oFTP->EditFTPPassword($ID, $Password) < 1)
{
	header("location: index.php?Notes=Cannot change password");
	exit();
}
header("location: index.php?Notes=Password changed");

?>


