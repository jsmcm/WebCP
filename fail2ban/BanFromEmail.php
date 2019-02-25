<?php
error_reporting(E_ALL);

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oFirewall = new Firewall();


$oFirewall->ManualBan($_REQUEST["IP"]);

?>

Done... Probably. You should check in the control panel
