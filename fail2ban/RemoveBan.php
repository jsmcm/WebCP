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
$Service = $_REQUEST["Service"];

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



	if ( $Service == "webcp-manual" ) {
		if ( file_exists(dirname(__DIR__)."/nm/".$IP.".ban") ) {
			unlink(dirname(__DIR__)."/nm/".$IP.".ban");
		}
	}

	$IP = str_replace("/", "_", $IP);

	file_put_contents(dirname(__DIR__)."/nm/".$IP.".unban", $Service);

	$x = 0;
        while( file_exists( dirname(__DIR__)."/nm/".$IP.".unban" ) ) {
                if($x++ > 20)
                        break;

                sleep(2);
        }

header("Location: index.php?Notes=Check%20in%202%20mins%20to%20make%20sure%20its%20been%20deleted");

