<?php
session_start();
error_reporting(E_ALL);

function validateIP($ip){
    return inet_pton($ip) !== false;
}

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oFirewall = new Firewall();
$oReseller = new Reseller();

if(! file_exists($_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp"))
{
	mkdir($_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp", 0755);
}

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$IP = $_POST["IP"];

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



if( validateIP($IP) == false )
{
	header("Location: index.php?Notes=Invalid%20IP%20address%20given.%20Use%20IPv4%20or%20IPv6%20format");
	exit();
}




	$oFirewall->ManualBan($IP);

        $x = 0;
        while(file_exists($_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp/add.ban"))
        {
                if($x++ > 20)
                        break;

                sleep(3);
        }

        $x = 0;
        while(file_exists($_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp/add.working"))
        {
                if($x++ > 20)
                        break;

                sleep(3);
        }

header("Location: index.php?Notes=Check%20in%202%20mins%20to%20make%20sure%20its%20been%20added%20to%20this%20list");

?>


