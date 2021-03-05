<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oEmail = new Email();

$oUser->Logout();
$oEmail->Logout();

header("Location: /index.php");
