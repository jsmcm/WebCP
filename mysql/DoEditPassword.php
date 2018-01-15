<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oMySQL = new MySQL();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$ID = $_POST["id"];
$Password = $_POST["Password"];

$MySQLUserName = "";
$MySQLDatabaseName = "";
$oMySQL->GetMySQLInfo($ID, $MySQLUserName, $MySQLDatabaseName);

$Role = $oUser->Role;



if($oMySQL->ChangePassword($MySQLUserName, $Password) < 1)
{
	header("location: index.php?NoteType=Error&Notes=Cannot change password");
	exit();
}
header("location: index.php?NoteType=Success&Notes=Password changed");

?>


