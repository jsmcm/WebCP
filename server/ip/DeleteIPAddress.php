<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.DNS.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");

$oDNS = new DNS();
$oDomain = new Domain();

if($oDNS->DeleteIPAddress($_REQUEST["IPAddress"]) == true)
{
}

header("Location: index.php?NoteType=success&Notes=IP Address Removed");
?>
