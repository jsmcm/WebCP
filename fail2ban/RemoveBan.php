<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Firewall.php");
$oFirewall = new Firewall();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Reseller.php");
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


//print "ID: ".$ID."<br>";
//print "IP: ".$IP."<br>";
//print "Service: ".$Service."<br>";

$oFirewall->ManualUnban($IP);
//exit();
        $x = 0;
        while(file_exists($_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp/remove.ban"))
        {
                if($x++ > 20)
                        break;

                sleep(3);
        }

        $x = 0;
        while(file_exists($_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp/remove.working"))
        {
                if($x++ > 20)
                        break;

                sleep(3);
        }

header("Location: index.php?Notes=Check%20in%202%20mins%20to%20make%20sure%20its%20been%20deleted");

?>


