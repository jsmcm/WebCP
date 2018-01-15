<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");

$oUser = new User();

print $oUser->CreateUserName(strtolower($_REQUEST["InputName"]));
?>

