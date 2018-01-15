<?php
session_start();

function __autoload($classname)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oEmail = new Email();

$oUser->Logout();
$oEmail->Logout();

header("Location: /index.php");

?>
