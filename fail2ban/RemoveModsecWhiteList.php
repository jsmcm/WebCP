<?php
session_start();

if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/")) {
    mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oFirewall = new Firewall();
$oDomain = new Domain();
$oSettings = new Settings();
$oReseller = new Reseller();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}

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


$ID = $_REQUEST["ID"];

$oFirewall->GetModsecWhiteListDetail($ID, $ModsecID, $HostName, $URI, $oUser->Role, $oUser->ClientID);
$oFirewall->DeleteModsecWhitelist($ID);

		if($HostName == "global")
		{
			touch($_SERVER["DOCUMENT_ROOT"]."/nm/modsec_gw");

		}
		else
		{
			$AncestorID = $oDomain->GetDomainIDFromDomainName($HostName);
			$AncestorID = $oDomain->GetParentDomainIDRecursive($AncestorID);

	                $oDomain->DeleteDomainFile($AncestorID);
	                $oDomain->MakeDomainFile($AncestorID);
			//print "Calling MakeDomainFile(".$AncestorID.")<br>";
		}

		header("Location: ViewModsecWhiteList.php?Notes=The whitelist was deleted&NoteType=Success");
		exit();

?>
