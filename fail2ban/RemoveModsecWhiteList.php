<?php
session_start();

                if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/"))
                {
                        mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
                }


require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Firewall.php");
$oFirewall = new Firewall();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Reseller.php");
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
