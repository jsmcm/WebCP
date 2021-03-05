<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oFirewall = new Firewall();
$oReseller = new Reseller();

if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp"))
{
	mkdir($_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp", 0755);
}

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$ID = $_REQUEST["ID"];
$IP = $_REQUEST["IP"];

$Role = $oUser->Role;
if($oUser->Role != "admin")
{
        $FirewallControl = "";
        if($oUser->Role == "reseller")
        {
                $FirewallControl = $oReseller->GetResellerSetting($oUser->ClientID, "FirewallControl");
        }
        if($FirewallControl != "on")
        {
                header("Location: /index.php");
                exit();
        }
}

	$IP = str_replace("/", "_", $IP);

	if ( file_exists(dirname(__DIR__)."/nm/".$IP.".permban") ) {
		unlink(dirname(__DIR__)."/nm/".$IP.".permban");
	}

	file_put_contents(dirname(__DIR__)."/nm/".$IP.".permunban", "");

	$x = 0;
        while( file_exists( dirname(__DIR__)."/nm/".$IP.".permunban" ) ) {
                if($x++ > 20)
                        break;

                sleep(2);
        }

header("Location: perm.php?Notes=Check%20in%202%20mins%20to%20make%20sure%20its%20been%20deleted");

