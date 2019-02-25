<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();

print $oUser->CreateUserName(strtolower($_REQUEST["InputName"]));

