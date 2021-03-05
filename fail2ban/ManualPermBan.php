<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
       
$oUser = new User();
$oFirewall = new Firewall();
$oReseller = new Reseller();

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
        header("Location: /index.php");
        exit();
}

$IP = $_POST["IP"];

$Role = $oUser->Role;

if($oUser->Role != "admin") {
        $FirewallControl = "";
        if($oUser->Role == "reseller") {
                $FirewallControl = $oReseller->GetResellerSetting($oUser->ClientID, "FirewallControl");
        }
        if($FirewallControl != "on") {
                header("Location: /index.php");
                exit();
        }
}


	$IP = str_replace( "/", "_", $IP );
	file_put_contents(dirname(__DIR__)."/nm/".$IP.".permban", "");

        $x = 0;
        while(file_exists(dirname(__DIR__)."/nm/".$IP.".permban")) {
                if($x++ > 20)
                        break;

                sleep(3);
        }

header("Location: perm.php?Notes=Check%20in%202%20mins%20to%20make%20sure%20its%20been%20added%20to%20this%20list");

