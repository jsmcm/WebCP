<?php
error_reporting(E_ALL);

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oFirewall = new Firewall();


$oFirewall->ManualBan($_REQUEST["IP"]);

?>

Done... Probably. You should check in the control panel
