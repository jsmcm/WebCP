<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
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


