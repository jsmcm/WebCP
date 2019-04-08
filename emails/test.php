<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oEmail = new Email();

$oEmail->RecreateUserForwardFile(4);

