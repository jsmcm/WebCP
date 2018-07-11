<?php
error_reporting(E_ALL);

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Firewall.php");
$oFirewall = new Firewall();


$oFirewall->ManualBan($_REQUEST["IP"]);

?>

Done... Probably. You should check in the control panel
