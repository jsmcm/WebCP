<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oDNS = new DNS();
$oDomain = new Domain();

if($oDNS->DeleteIPAddress($_REQUEST["IPAddress"]) == true)
{
}

header("Location: index.php?NoteType=success&Notes=IP Address Removed");
